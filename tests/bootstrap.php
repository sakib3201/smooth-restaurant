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

// Define test constants.
if ( ! defined( 'SMOOTH_RESTAURANT_TESTS' ) ) {
	define( 'SMOOTH_RESTAURANT_TESTS', true );
}
