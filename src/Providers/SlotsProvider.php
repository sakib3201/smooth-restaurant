<?php
/**
 * Slots service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Domains\Slots\SlotService;

/**
 * Class SlotsProvider
 *
 * Binds slot services in register() (bind-only) and hooks slot
 * availability in boot() on diner frontend requests only.
 */
final class SlotsProvider extends ServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * Bind-only: no hooks, no database access, no translation calls.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( SlotService::class );
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

		add_action( 'init', array( $this, 'registerSlotRules' ) );
		$this->markBooted();
	}

	/**
	 * Register slot availability rules.
	 *
	 * Shell: real slot-rule wiring lands with the slots domain issue.
	 *
	 * @return void
	 */
	public function registerSlotRules(): void {
	}
}
