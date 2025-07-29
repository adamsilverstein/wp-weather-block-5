/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch weather data from the API.
 *
 * @param {Object} attributes The block attributes.
 * @return {Promise} A promise that resolves with the weather data.
 */
export function fetchWeather( attributes ) {
	const { location, units } = attributes;
	return apiFetch( { path: `/weather-block/v1/weather?location=${ location }&units=${ units }` } );
}
