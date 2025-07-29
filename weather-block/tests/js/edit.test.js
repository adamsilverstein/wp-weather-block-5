/**
 * WordPress dependencies
 */
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';

/**
 * Internal dependencies
 */
import Edit from '../../src/weather-block/edit';

describe( 'Edit', () => {
	it( 'renders the location input', () => {
		const attributes = {
			location: 'London',
			units: 'metric',
			displayMode: 'light',
		};
		const setAttributes = jest.fn();

		render( <Edit attributes={ attributes } setAttributes={ setAttributes } /> );

		const locationInput = screen.getByLabelText( 'Location' );
		expect( locationInput ).toBeInTheDocument();
	} );

	it( 'calls setAttributes when the location is changed', async () => {
		const attributes = {
			location: 'London',
			units: 'metric',
			displayMode: 'light',
		};
		const setAttributes = jest.fn();

		render( <Edit attributes={ attributes } setAttributes={ setAttributes } /> );

		const locationInput = screen.getByLabelText( 'Location' );
		await userEvent.clear( locationInput );
		await userEvent.type( locationInput, 'Paris' );

		expect( setAttributes ).toHaveBeenCalled();
	} );
} );
