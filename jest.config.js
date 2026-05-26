const path = require( 'path' );

module.exports = {
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	testEnvironment: 'jsdom',
	setupFilesAfterEnv: [
		'<rootDir>/tests/js/setupTests.ts',
	],
	moduleNameMapper: {
		'^@/admin/(.*)$': '<rootDir>/assets/src/admin/$1',
		'^@/frontend/(.*)$': '<rootDir>/assets/src/frontend/$1',
		'^@/shared/(.*)$': '<rootDir>/assets/src/shared/$1',
		'^@/blocks/(.*)$': '<rootDir>/assets/src/blocks/$1',
	},
	transform: {
		'^.+\\.tsx?$': 'ts-jest',
	},
	coverageDirectory: '<rootDir>/coverage/js',
};
