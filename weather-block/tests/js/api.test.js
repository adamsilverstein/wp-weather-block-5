/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { fetchWeather } from '../../src/weather-block/api';

jest.mock( '@wordpress/api-fetch' );

describe( 'fetchWeather', () => {
	it( 'should fetch weather data', async () => {
		apiFetch.mockResolvedValue( { city: 'London' } );

		const data = await fetchWeather( { location: 'London', units: 'metric' } );

		expect( apiFetch ).toHaveBeenCalledWith( {
			path: '/weather-block/v1/weather?location=London&units=metric',
		} );
		expect( data.city ).toBe( 'London' );
	} );
} );
