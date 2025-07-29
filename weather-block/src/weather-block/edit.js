/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RadioControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import React from 'react';

/**
 * Internal dependencies
 */
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

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'weather-block' ) }>
					<TextControl
						label={ __( 'Location', 'weather-block' ) }
						value={ location }
						onChange={ ( value ) =>
							setAttributes( { location: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Units', 'weather-block' ) }
						help={
							units === 'metric'
								? __( 'Celsius', 'weather-block' )
								: __( 'Fahrenheit', 'weather-block' )
						}
						checked={ units === 'imperial' }
						onChange={ () =>
							setAttributes( {
								units:
									units === 'metric' ? 'imperial' : 'metric',
							} )
						}
					/>
					<RadioControl
						label={ __( 'Display Mode', 'weather-block' ) }
						selected={ displayMode }
						options={ [
							{
								label: __( 'Light', 'weather-block' ),
								value: 'light',
							},
							{
								label: __( 'Dark', 'weather-block' ),
								value: 'dark',
							},
							{
								label: __( 'Auto', 'weather-block' ),
								value: 'auto',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { displayMode: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block="create-block/weather-block"
				attributes={ attributes }
			/>
		</div>
	);
}