<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

// Define WordPress test directory or use a fallback.
if ( ! defined( 'WP_TESTS_DIR' ) ) {
	$env_tests_dir = getenv( 'WP_TESTS_DIR' );
	define( 'WP_TESTS_DIR', false !== $env_tests_dir ? $env_tests_dir : __DIR__ . '/../vendor/wordpress/wordpress/tests/phpunit' );
}

// Load Composer autoloader.
$autoloader = __DIR__ . '/../vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

// Load WordPress test environment if available.
$wp_tests_load = WP_TESTS_DIR . '/includes/bootstrap.php';
if ( file_exists( $wp_tests_load ) ) {
	require_once $wp_tests_load;
}

// Load minimal WP function stubs for suites running without WordPress.
// Each stub is guarded by function_exists(), so the real WordPress
// functions above always win when the test environment is present.
require_once __DIR__ . '/Support/WpStubs.php';

// In the lightweight suite context (no WordPress test library), route
// error_log to a temp file so intentional runtime logs (e.g. skipped
// Pro providers) do not pollute PHPUnit output: process-isolated tests
// fail on any printed output. Production logging is unchanged, and
// Plugin::skippedProviders() keeps skips observable to tests.
$wp_tests_bootstrap = (string) WP_TESTS_DIR . '/includes/bootstrap.php';
if ( ! file_exists( $wp_tests_bootstrap ) ) {
	ini_set( 'error_log', sys_get_temp_dir() . '/smooth-restaurant-unit-error.log' );
}

// Define test constants.
if ( ! defined( 'SMOOTH_RESTAURANT_TESTS' ) ) {
	define( 'SMOOTH_RESTAURANT_TESTS', true );
}
