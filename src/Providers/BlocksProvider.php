<?php
/**
 * Blocks service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class BlocksProvider
 *
 * Hooks block registration in boot() on diner frontend (view) requests
 * only, per the boot matrix. Shell: block.json wiring and any editor
 * context land with the blocks follow-up issue.
 */
final class BlocksProvider extends ServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * Bind-only: no hooks, no database access, no translation calls.
	 * Shell: no block bindings yet.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
	}

	/**
	 * Boot the provider after all providers are registered.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		if ( $this->isBackendRequest() ) {
			return;
		}

		add_action( 'init', array( $this, 'registerBlocks' ) );
		$this->markBooted();
	}

	/**
	 * Register blocks.
	 *
	 * Shell: real block registration lands with the blocks follow-up issue.
	 *
	 * @return void
	 */
	public function registerBlocks(): void {
	}
}
