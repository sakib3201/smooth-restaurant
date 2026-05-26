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

// Delete all plugin options.
$options = [
	'smooth_restaurant_db_version',
	'smooth_restaurant_currency',
	'smooth_restaurant_currency_position',
	'smooth_restaurant_decimal_separator',
	'smooth_restaurant_thousand_separator',
	'smooth_restaurant_date_format',
	'smooth_restaurant_time_format',
	'smooth_restaurant_reservation_interval',
	'smooth_restaurant_order_prefix',
	'smooth_restaurant_enable_online_orders',
];

foreach ($options as $option) {
	delete_option($option);
}

// Delete custom post types and their posts.
$post_types = [
	'smooth_restaurant_menu_item',
	'smooth_restaurant_reservation',
	'smooth_restaurant_order',
];

foreach ($post_types as $post_type) {
	$posts = get_posts(
		[
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		]
	);

	foreach ($posts as $post_id) {
		wp_delete_post($post_id, true);
	}
}

// Delete custom roles.
$roles = [
	'smooth_restaurant_staff',
	'smooth_restaurant_kitchen',
	'smooth_restaurant_manager',
	'smooth_restaurant_driver',
];

foreach ($roles as $role) {
	remove_role($role);
}

// Remove plugin capabilities from the administrator role.
$admin = get_role('administrator');
if ($admin instanceof WP_Role) {
	$plugin_caps = [
		'edit_smooth_restaurant_menu_items',
		'edit_others_smooth_restaurant_menu_items',
		'publish_smooth_restaurant_menu_items',
		'read_smooth_restaurant_menu_item',
		'delete_smooth_restaurant_menu_items',
		'edit_smooth_restaurant_reservations',
		'edit_others_smooth_restaurant_reservations',
		'publish_smooth_restaurant_reservations',
		'read_smooth_restaurant_reservation',
		'delete_smooth_restaurant_reservations',
		'edit_smooth_restaurant_orders',
		'edit_others_smooth_restaurant_orders',
		'publish_smooth_restaurant_orders',
		'read_smooth_restaurant_order',
		'delete_smooth_restaurant_orders',
		'manage_smooth_restaurant_settings',
	];

	foreach ($plugin_caps as $cap) {
		$admin->remove_cap($cap);
	}
}

// Delete plugin transients.
$transients = [
	'smooth_restaurant_menu_cache',
	'smooth_restaurant_order_stats',
	'smooth_restaurant_reservation_summary',
	'smooth_restaurant_daily_revenue',
];

foreach ($transients as $transient) {
	delete_transient($transient);
}
