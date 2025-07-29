<?php

class Weather_Block_Test extends WP_UnitTestCase {
	public function test_rest_endpoint() {
		$request = new WP_REST_Request( 'GET', '/weather-block/v1/weather' );
		$request->set_query_params( array( 'location' => 'London' ) );
		$response = rest_do_request( $request );
		$this->assertEquals( 200, $response->get_status() );
	}
}
