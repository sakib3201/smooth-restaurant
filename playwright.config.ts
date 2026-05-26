import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for WordPress E2E tests.
 *
 * Uses @wordpress/env for environment lifecycle and follows the
 * WordPress E2E test utils pattern.
 *
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
	// Test directory
	testDir: './tests/e2e',

	// Run tests in files in parallel
	fullyParallel: false,

	// Fail the build on CI if you accidentally left test.only in the source code
	forbidOnly: !!process.env.CI,

	// Retry on CI only
	retries: process.env.CI ? 2 : 0,

	// Opt out of parallel tests because WordPress state is shared
	workers: 1,

	// Reporter to use
	reporter: [
		['list'],
		['html', { open: 'never' }],
	],

	// Shared settings for all the projects below
	use: {
		// Base URL to use in actions like `await page.goto('/')`
		baseURL: 'http://localhost:8888',

		// Collect trace when retrying the failed test
		trace: 'on-first-retry',

		// Capture screenshot on failure
		screenshot: 'only-on-failure',
	},

	// Global setup / teardown for wp-env lifecycle
	globalSetup: require.resolve('./tests/e2e/fixtures/index.ts'),
	globalTeardown: require.resolve('./tests/e2e/fixtures/index.ts'),

	// Configure projects for major browsers
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
		{
			name: 'chromium-mobile',
			use: { ...devices['Pixel 5'] },
		},
	],
});
