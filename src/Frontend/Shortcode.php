<?php
/**
 * Frontend shortcode registration.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Frontend;

/**
 * Class Shortcode
 *
 * Registers the [smooth_menu] shortcode and enqueues frontend assets.
 */
class Shortcode {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'smooth_menu', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the shortcode output.
	 *
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ): string {
		$this->enqueue_assets();

		return '<div class="smooth-restaurant-frontend-root"></div>';
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	private function enqueue_assets(): void {
		$asset_file = SR_PLUGIN_DIR . 'assets/build/frontend/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => SR_VERSION,
			);

		wp_enqueue_script(
			'smooth-restaurant-frontend',
			SR_PLUGIN_URL . 'assets/build/frontend/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'smooth-restaurant-frontend',
			SR_PLUGIN_URL . 'assets/build/frontend/style.css',
			array(),
			$asset['version']
		);

		wp_localize_script(
			'smooth-restaurant-frontend',
			'smoothRestaurantFrontend',
			array(
				'restUrl' => esc_url_raw( rest_url() ),
			)
		);
	}
}
