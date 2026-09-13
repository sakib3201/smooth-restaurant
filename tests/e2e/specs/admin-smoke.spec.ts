import { test, expect } from '@playwright/test';
import { loginAsAdmin } from '../utils/login';

/**
 * Admin smoke tests for Smooth Restaurant.
 *
 * Ensures wp-admin loads.
 */
test.describe( 'Admin Smoke Tests', () => {
	test.beforeEach( async ( { page } ) => {
		await loginAsAdmin( page );
	} );

	test( 'wp-admin dashboard loads', async ( { page } ) => {
		await page.goto( '/wp-admin' );
		await expect( page ).toHaveTitle( /Dashboard/ );
		await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
	} );
} );
