/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Fetch weather data from the API.
 *
 * @param {Object} attributes The block attributes.
 * @return {Promise} A promise that resolves with the weather data.
 */
export function fetchWeather( attributes ) {
	const { location, units } = attributes;
	const path = addQueryArgs( '/weather-block/v1/weather', { location, units } );
	return apiFetch( { path } );
}
