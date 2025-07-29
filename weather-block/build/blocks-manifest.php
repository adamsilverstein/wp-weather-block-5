<?php
// This file is generated. Do not modify it manually.
return array(
	'weather-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/weather-block',
		'version' => '0.1.0',
		'title' => 'Weather Block',
		'category' => 'widgets',
		'icon' => 'cloud',
		'description' => 'A block to display current weather conditions.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'location' => array(
				'type' => 'string',
				'default' => ''
			),
			'units' => array(
				'type' => 'string',
				'default' => 'metric'
			),
			'displayMode' => array(
				'type' => 'string',
				'default' => 'light'
			)
		),
		'textdomain' => 'weather-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
