import React from 'react';

export const PanelBody = ( { children } ) => <div>{ children }</div>;
export const TextControl = ( { label, value, onChange } ) => (
	<label>
		{ label }
		<input
			type="text"
			value={ value }
			onChange={ ( event ) => onChange( event.target.value ) }
		/>
	</label>
);
export const ToggleControl = ( { label, help, checked, onChange } ) => (
	<div>
		<label>{ label }</label>
		<input type="checkbox" checked={ checked } onChange={ onChange } />
		<span>{ help }</span>
	</div>
);
export const RadioControl = ( { label, selected, options, onChange } ) => (
	<div>
		<label>{ label }</label>
		{ options.map( ( option ) => (
			<label key={ option.value }>
				<input
					type="radio"
					value={ option.value }
					checked={ selected === option.value }
					onChange={ () => onChange( option.value ) }
				/>
				{ option.label }
			</label>
		) ) }
	</div>
);