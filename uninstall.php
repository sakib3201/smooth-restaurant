<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SmoothRestaurant
 */

// If uninstall not called from WordPress, exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Check if the user has the required capability.
if (! current_user_can('activate_plugins')) {
	return;
}

/**
 * Allow users to opt out of data deletion on uninstall.
 *
 * @param bool $keep_data Whether to keep data. Default false.
 */
$keep_data = apply_filters('smooth_restaurant_uninstall_keep_data', false);

if (true === $keep_data) {
	return;
}

// Rebuild issues add option, table, role and transient cleanup here.
delete_option('smooth_restaurant_db_version');
