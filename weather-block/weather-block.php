<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A block to display current weather conditions.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       weather-block
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// For testing purposes, define the key here.
// In a real plugin, this would be in wp-config.php or a settings page.
define( 'WEATHER_BLOCK_API_KEY', 'your_provided_api_key_here' );

/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function create_block_weather_block_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}", array( 'render_callback' => 'weather_block_render_block' ) );
	}
}
add_action( 'init', 'create_block_weather_block_block_init' );

/**
 * Adds the settings page.
 */
function weather_block_add_settings_page() {
	add_options_page(
		__( 'Weather Block Settings', 'weather-block' ),
		__( 'Weather Block', 'weather-block' ),
		'manage_options',
		'weather-block',
		'weather_block_render_settings_page'
	);
}
add_action( 'admin_menu', 'weather_block_add_settings_page' );

/**
 * Renders the settings page.
 */
function weather_block_render_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'weather_block' );
			do_settings_sections( 'weather_block' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Registers the settings.
 */
function weather_block_register_settings() {
	register_setting(
		'weather_block',
		'weather_block_api_key',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	add_settings_section(
		'weather_block_api_settings',
		__( 'API Settings', 'weather-block' ),
		'weather_block_api_settings_section_callback',
		'weather_block'
	);

	add_settings_field(
		'weather_block_api_key',
		__( 'OpenWeatherMap API Key', 'weather-block' ),
		'weather_block_api_key_field_callback',
		'weather_block',
		'weather_block_api_settings'
	);
}
add_action( 'admin_init', 'weather_block_register_settings' );

/**
 * Renders the API settings section.
 */
function weather_block_api_settings_section_callback() {
	echo '<p>' . esc_html__( 'Enter your OpenWeatherMap API key below. You can get one for free from the OpenWeatherMap website.', 'weather-block' ) . '</p>';
	echo '<p><a href="https://openweathermap.org/api" target="_blank" rel="noopener noreferrer">' . esc_html__( 'OpenWeatherMap API Documentation', 'weather-block' ) . '</a></p>';
}

/**
 * Renders the API key field.
 */
function weather_block_api_key_field_callback() {
	$api_key = get_option( 'weather_block_api_key' );
	?>
	<input type="text" name="weather_block_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
	<?php
}

/**
 * Registers the REST API endpoint.
 */
function weather_block_register_rest_endpoint() {
	register_rest_route(
		'weather-block/v1',
		'/weather',
		array(
			'methods'             => 'GET',
			'callback'            => 'weather_block_fetch_weather_data',
			'permission_callback' => '__return_true', // TODO: Add a proper permission check.
		)
	);
}
add_action( 'rest_api_init', 'weather_block_register_rest_endpoint' );

/**
 * Fetches the weather data.
 *
 * @param WP_REST_Request $request The REST API request.
 * @return WP_REST_Response The REST API response.
 */
function weather_block_fetch_weather_data( $request ) {
	$location = $request->get_param( 'location' );
	$units    = $request->get_param( 'units' );

	if ( ! $location ) {
		return new WP_Error( 'no_location', __( 'Please provide a location.', 'weather-block' ), array( 'status' => 400 ) );
	}

	$transient_key = 'weather_block_' . md5( $location . $units );
	$weather_data  = get_transient( $transient_key );

	if ( false === $weather_data ) {
		$api_key = get_option( 'weather_block_api_key' );
		if ( ! $api_key ) {
			$api_key = defined( 'WEATHER_BLOCK_API_KEY' ) ? WEATHER_BLOCK_API_KEY : '';
		}

		if ( ! $api_key ) {
			return new WP_Error( 'no_api_key', __( 'Please provide an API key.', 'weather-block' ), array( 'status' => 400 ) );
		}

		$response = wp_remote_get(
			sprintf(
				'https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s',
				urlencode( $location ),
				urlencode( $units ),
				urlencode( $api_key )
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Weather Block API Error: ' . $response->get_error_message() );
			return new WP_Error( 'api_error', __( 'Could not fetch weather data.', 'weather-block' ), array( 'status' => 500 ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			error_log( 'Weather Block API Error: ' . $data['message'] );
			return new WP_Error( 'api_error', __( 'Could not fetch weather data.', 'weather-block' ), array( 'status' => 500 ) );
		}

		$weather_data = array(
			'city'        => $data['name'],
			'temperature' => $data['main']['temp'],
			'icon'        => $data['weather'][0]['icon'],
			'description' => $data['weather'][0]['description'],
			'humidity'    => $data['main']['humidity'],
		);

		set_transient( $transient_key, $weather_data, 15 * MINUTE_IN_SECONDS );
	}

	return new WP_REST_Response( $weather_data, 200 );
}

/**
 * Renders the block on the server.
 *
 * @param array $attributes The block attributes.
 * @return string The block HTML.
 */
function weather_block_render_block( $attributes ) {
	$location = $attributes['location'];
	$units    = $attributes['units'];

	if ( ! $location ) {
		return '';
	}

	$transient_key = 'weather_block_' . md5( $location . $units );
	$weather_data  = get_transient( $transient_key );

	if ( false === $weather_data ) {
		// Don't fetch data on the server, as it will slow down page loads.
		// The data will be fetched on the client-side.
		return '';
	}

	$display_mode = $attributes['displayMode'];
	$classes      = "weather-block-display {$display_mode}";

	ob_start();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<h3><?php echo esc_html( $weather_data['city'] ); ?></h3>
		<p class="temp"><?php echo esc_html( $weather_data['temperature'] ); ?>&deg;</p>
		<img src="<?php echo esc_url( sprintf( 'https://openweathermap.org/img/wn/%s.png', $weather_data['icon'] ) ); ?>" alt="<?php echo esc_attr( $weather_data['description'] ); ?>" />
		<p class="description"><?php echo esc_html( $weather_data['description'] ); ?></p>
		<p class="humidity"><?php esc_html_e( 'Humidity:', 'weather-block' ); ?> <?php echo esc_html( $weather_data['humidity'] ); ?>%</p>
	</div>
	<?php
	return ob_get_clean();
}
