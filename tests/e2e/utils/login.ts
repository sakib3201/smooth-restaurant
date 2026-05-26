import type { Page } from '@playwright/test';

/**
 * Log in as the WordPress admin user.
 *
 * @param page   Playwright Page instance.
 * @param user   Username (default: admin).
 * @param pass   Password (default: password).
 */
export async function loginAsAdmin(
	page: Page,
	user: string = 'admin',
	pass: string = 'password'
): Promise<void> {
	await page.goto('/wp-login.php');
	await page.fill('#user_login', user);
	await page.fill('#user_pass', pass);
	await page.click('#wp-submit');
	await page.waitForURL(/wp-admin/);
}
