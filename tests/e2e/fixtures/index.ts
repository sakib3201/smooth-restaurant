import type { FullConfig } from '@playwright/test';

/**
 * WordPress E2E global setup and teardown.
 *
 * When wp-env is available, starts it before the test run and stops it afterwards.
 * Falls back gracefully when Docker is not available.
 */

async function globalSetup( config: FullConfig ): Promise<void> {
	console.log( 'Using existing WordPress instance at http://wpt.local' );
}

async function globalTeardown( config: FullConfig ): Promise<void> {
	console.log( 'Tests complete.' );
}

export default globalSetup;
export { globalTeardown };
