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
		register_block_type( __DIR__ . "/build/{$block_type}", array( 'render_callback' => 'weather_block_render_block_server_side' ) );
	}
}
add_action( 'init', 'create_block_weather_block_block_init' );

/**
 * Fetches the weather data and renders the block on the server.
 * This function is used as a fallback for older WordPress versions or when
 * client-side rendering is not desired.
 *
 * @param array $attributes The block attributes.
 * @return string The block HTML.
 */
function weather_block_render_block_server_side( $attributes ) {
	$location = $attributes['location'];
	$units    = $attributes['units'];

	if ( ! $location ) {
		return '';
	}

	$transient_key = 'weather_block_' . md5( $location . $units );
	$weather_data  = get_transient( $transient_key );

	if ( false === $weather_data ) {
		$api_key = get_option( 'weather_block_api_key' );
		if ( ! $api_key ) {
			$api_key = defined( 'WEATHER_BLOCK_API_KEY' ) ? WEATHER_BLOCK_API_KEY : '';
		}

		if ( ! $api_key ) {
			return '<p>' . esc_html__( 'Please provide an API key in the Weather Block settings.', 'weather-block' ) . '</p>';
		}

		$response = wp_remote_get(
			sprintf(
				'https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s',
				rawurlencode( $location ),
				rawurlencode( $units ),
				rawurlencode( $api_key )
			)
		);

		if ( is_wp_error( $response ) ) {
			return '<p>' . esc_html__( 'Could not fetch weather data.', 'weather-block' ) . '</p>';
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '<p>' . esc_html__( 'Could not fetch weather data.', 'weather-block' ) . '</p>';
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

/**
 * Registers the REST API endpoint.
 */
function weather_block_register_rest_endpoint() {
	register_rest_route(
		'weather-block/v1',
		'/weather',
		array(
			'methods'             => 'GET',
			'callback'            => 'weather_block_rest_endpoint_callback',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'weather_block_register_rest_endpoint' );

/**
 * The callback for the REST API endpoint.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function weather_block_rest_endpoint_callback( $request ) {
	$location = $request->get_param( 'location' );
	$units    = $request->get_param( 'units' );

	if ( ! $location ) {
		return new WP_REST_Response( array( 'error' => 'Missing location.' ), 400 );
	}

	// Nonce check.
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( array( 'error' => 'Invalid nonce.' ), 403 );
	}

	$weather_data = weather_block_get_weather_data( $location, $units );

	if ( is_wp_error( $weather_data ) ) {
		return new WP_REST_Response( array( 'error' => $weather_data->get_error_message() ), 500 );
	}

	return new WP_REST_Response( $weather_data, 200 );
}

/**
 * Fetches weather data.
 *
 * @param string $location The location to fetch weather for.
 * @param string $units The units to use.
 * @return array|WP_Error The weather data or a WP_Error object.
 */
function weather_block_get_weather_data( $location, $units ) {
	$transient_key = 'weather_block_' . md5( $location . $units );
	$weather_data  = get_transient( $transient_key );

	if ( false === $weather_data ) {
		$api_key = get_option( 'weather_block_api_key' );
		if ( ! $api_key ) {
			$api_key = defined( 'WEATHER_BLOCK_API_KEY' ) ? WEATHER_BLOCK_API_KEY : '';
		}

		if ( ! $api_key ) {
			return new WP_Error( 'missing_api_key', __( 'Please provide an API key in the Weather Block settings.', 'weather-block' ) );
		}

		$response = wp_remote_get(
			sprintf(
				'https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s',
				rawurlencode( $location ),
				rawurlencode( $units ),
				rawurlencode( $api_key )
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', __( 'Could not fetch weather data.', 'weather-block' ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'api_error', __( 'Could not fetch weather data.', 'weather-block' ) );
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

	return $weather_data;
}
