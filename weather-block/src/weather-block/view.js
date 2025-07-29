/**
 * WordPress dependencies
 */
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch weather data from the API.
 *
 * @param {Object} attributes The block attributes.
 * @return {Promise} A promise that resolves with the weather data.
 */
async function fetchWeather( attributes ) {
	const { location, units } = attributes;
	const path = addQueryArgs( '/weather-block/v1/weather', {
		location,
		units,
	} );
	return apiFetch( { path } );
}

/**
 * Renders the weather block on the frontend.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const weatherBlocks = document.querySelectorAll( '.wp-block-create-block-weather-block' );

	weatherBlocks.forEach( async ( block ) => {
		const location = block.dataset.location;
		const units = block.dataset.units;
		const displayMode = block.dataset.displayMode;

		if ( ! location ) {
			return;
		}

		block.classList.add( displayMode );

		try {
			const weather = await fetchWeather( { location, units } );
			const html = `
				<h3>${ weather.city }</h3>
				<p class="temp">${ weather.temperature }&deg;</p>
				<img src="https://openweathermap.org/img/wn/${ weather.icon }.png" alt="${ weather.description }" />
				<p class="description">${ weather.description }</p>
				<p class="humidity">Humidity: ${ weather.humidity }%</p>
			`;
			block.innerHTML = html;
		} catch ( error ) {
			block.innerHTML = `<p class="error">Error: ${ error.message }</p>`;
		}
	} );
} );