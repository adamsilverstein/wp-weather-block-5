const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Weather Block', () => {
	test( 'should render the block in the editor', async ( { editor, page } ) => {
		await editor.canvas.locator( 'body' ).click();
		await editor.insertBlock( { name: 'create-block/weather-block' } );

		await expect( page.locator( '[data-type="create-block/weather-block"]' ) ).toBeVisible();
	} );
} );
