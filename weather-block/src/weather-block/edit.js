/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RadioControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import { fetchWeather } from './api';
import './editor.scss';

/**
 * The edit function.
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates block attributes.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { location, units, displayMode } = attributes;
	const [ weather, setWeather ] = useState( null );
	const [ error, setError ] = useState( null );

	useEffect( () => {
		if ( ! location ) {
			return;
		}

		setError( null );
		fetchWeather( attributes )
			.then( ( data ) => setWeather( data ) )
			.catch( ( err ) => setError( err.message ) );
	}, [ location, units ] );

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'weather-block' ) }>
					<TextControl
						label={ __( 'Location', 'weather-block' ) }
						value={ location }
						onChange={ ( value ) => setAttributes( { location: value } ) }
					/>
					<ToggleControl
						label={ __( 'Units', 'weather-block' ) }
						help={ units === 'metric' ? __( 'Celsius', 'weather-block' ) : __( 'Fahrenheit', 'weather-block' ) }
						checked={ units === 'imperial' }
						onChange={ () => setAttributes( { units: units === 'metric' ? 'imperial' : 'metric' } ) }
					/>
					<RadioControl
						label={ __( 'Display Mode', 'weather-block' ) }
						selected={ displayMode }
						options={ [
							{ label: __( 'Light', 'weather-block' ), value: 'light' },
							{ label: __( 'Dark', 'weather-block' ), value: 'dark' },
							{ label: __( 'Auto', 'weather-block' ), value: 'auto' },
					] }
						onChange={ ( value ) => setAttributes( { displayMode: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			{ error && <p className="error">{ error }</p> }
			{ ! location && <p>{ __( 'Please enter a location.', 'weather-block' ) }</p> }
			{ location && ! weather && ! error && <p>{ __( 'Loading...', 'weather-block' ) }</p> }
			{ weather && (
				<div className={ `weather-block-display ${ displayMode }` }>
					<h3>{ weather.city }</h3>
					<p className="temp">{ weather.temperature }&deg;</p>
					<img src={ `https://openweathermap.org/img/wn/${ weather.icon }.png` } alt={ weather.description } />
					<p className="description">{ weather.description }</p>
					<p className="humidity">{ __( 'Humidity:', 'weather-block' ) } { weather.humidity }%</p>
				</div>
			) }
			<ServerSideRender
				block="create-block/weather-block"
				attributes={ attributes }
			/>
		</div>
	);
}
