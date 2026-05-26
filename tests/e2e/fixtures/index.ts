import type { FullConfig } from '@playwright/test';
import { execSync } from 'child_process';

/**
 * WordPress E2E global setup and teardown.
 *
 * Starts wp-env before the test run and stops it afterwards.
 */

async function globalSetup(config: FullConfig): Promise<void> {
	console.log('Starting wp-env...');
	execSync('npx wp-env start', { stdio: 'inherit' });
}

async function globalTeardown(config: FullConfig): Promise<void> {
	console.log('Stopping wp-env...');
	execSync('npx wp-env stop', { stdio: 'inherit' });
}

export default globalSetup;
export { globalTeardown };
