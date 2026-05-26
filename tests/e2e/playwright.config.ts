import { defineConfig, devices } from '@playwright/test';

/**
 * Minimal Playwright configuration for the e2e test directory.
 *
 * This file can be used when running `playwright test` directly
 * from the tests/e2e directory. It imports and re-exports the
 * root configuration.
 */
export default defineConfig({
	testDir: '.',
	fullyParallel: false,
	workers: 1,
	reporter: [['list'], ['html', { open: 'never' }]],
	use: {
		baseURL: 'http://localhost:8888',
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},
	globalSetup: require.resolve('./fixtures/index.ts'),
	globalTeardown: require.resolve('./fixtures/index.ts'),
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
