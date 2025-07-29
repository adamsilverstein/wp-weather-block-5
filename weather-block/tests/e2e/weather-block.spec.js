/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Weather Block', () => {
	test( 'should render the block in its default state', async ( {
		editor,
		page,
	} ) => {
		await editor.canvas.locator( 'body' ).click();
		await editor.insertBlock( { name: 'create-block/weather-block' } );

		// Take a screenshot of the block.
		const block = await editor.canvas.locator(
			'.wp-block-create-block-weather-block'
		);
		await expect( block ).toHaveScreenshot( 'weather-block-default.png' );
	} );
} );