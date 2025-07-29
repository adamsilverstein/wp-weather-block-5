module.exports = {
	preset: '@wordpress/jest-preset-default',
	setupFilesAfterEnv: [ '<rootDir>/tests/js/jest-setup.js' ],
	moduleNameMapper: {
		'@wordpress/i18n': '<rootDir>/tests/js/mocks/i18n.js',
		'@wordpress/block-editor': '<rootDir>/tests/js/mocks/block-editor.js',
		'@wordpress/components': '<rootDir>/tests/js/mocks/components.js',
		'@wordpress/server-side-render': '<rootDir>/tests/js/mocks/server-side-render.js',
	},
};