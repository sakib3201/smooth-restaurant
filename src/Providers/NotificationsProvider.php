<?php
/**
 * Notifications service provider.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Providers;

use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\ServiceProvider;
use SmoothRestaurant\Domains\Notifications\NotificationService;

/**
 * Class NotificationsProvider
 *
 * Binds notification services in register() (bind-only) and hooks the
 * notification worker in boot() on cron requests only. Channel
 * implementations satisfy Contracts\NotifierInterface.
 */
final class NotificationsProvider extends ServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * Bind-only: no hooks, no database access, no translation calls.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( NotificationService::class );
	}

	/**
	 * Boot the provider after all providers are registered.
	 *
	 * @param Container $container The DI container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		if ( ! $this->isDoingCron() ) {
			return;
		}

		add_action( 'init', array( $this, 'scheduleWorker' ) );
		$this->markBooted();
	}

	/**
	 * Schedule the notification worker.
	 *
	 * Shell: real worker wiring lands with the notifications domain issue.
	 *
	 * @return void
	 */
	public function scheduleWorker(): void {
	}
}
