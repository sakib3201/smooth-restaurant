<?php
/**
 * Admin menu registration.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Admin;

/**
 * Class AdminMenu
 *
 * Registers the top-level admin menu and submenu pages for the plugin.
 */
class AdminMenu {

	/**
	 * The menu slug used for the top-level page.
	 *
	 * @var string
	 */
	private string $menu_slug = 'smooth-restaurant';

	/**
	 * Capability required to access the admin pages.
	 *
	 * @var string
	 */
	private string $capability = 'manage_options';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Add the top-level menu and submenu pages.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'Smooth Restaurant', 'smooth-restaurant' ),
			__( 'Smooth Restaurant', 'smooth-restaurant' ),
			$this->capability,
			$this->menu_slug,
			array( $this, 'render_admin_page' ),
			'dashicons-food',
			25
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Dashboard', 'smooth-restaurant' ),
			__( 'Dashboard', 'smooth-restaurant' ),
			$this->capability,
			$this->menu_slug,
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Menu Items', 'smooth-restaurant' ),
			__( 'Menu Items', 'smooth-restaurant' ),
			$this->capability,
			'smooth-restaurant-menu-items',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Orders', 'smooth-restaurant' ),
			__( 'Orders', 'smooth-restaurant' ),
			$this->capability,
			'smooth-restaurant-orders',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Reservations', 'smooth-restaurant' ),
			__( 'Reservations', 'smooth-restaurant' ),
			$this->capability,
			'smooth-restaurant-reservations',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Settings', 'smooth-restaurant' ),
			__( 'Settings', 'smooth-restaurant' ),
			$this->capability,
			'smooth-restaurant-settings',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin page container.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		echo '<div id="smooth-restaurant-admin"></div>';
	}
}
