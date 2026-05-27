const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve?.alias,
			'@/admin': path.resolve( __dirname, 'assets/src/admin' ),
			'@/frontend': path.resolve( __dirname, 'assets/src/frontend' ),
			'@/shared': path.resolve( __dirname, 'assets/src/shared' ),
			'@/blocks': path.resolve( __dirname, 'assets/src/blocks' ),
		},
	},
	entry: {
		index: path.resolve( __dirname, 'assets/src/admin/index.tsx' ),
		reservations: path.resolve( __dirname, 'assets/src/admin/reservations.ts' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'assets/build/admin' ),
		filename: '[name].js',
	},
};
