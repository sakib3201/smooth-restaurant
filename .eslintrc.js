module.exports = {
	root: true,
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	overrides: [
		{
			files: [ '**/*.ts', '**/*.tsx' ],
			rules: {
				// TypeScript itself validates imports; the default resolver
				// does not understand the tsconfig path aliases used in this project.
				'import/no-unresolved': 'off',
			},
		},
	],
};
