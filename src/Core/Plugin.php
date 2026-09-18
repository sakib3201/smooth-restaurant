<?php

/**
 * Main plugin class.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

use SmoothRestaurant\Providers\AdminProvider;
use SmoothRestaurant\Providers\AssetsProvider;
use SmoothRestaurant\Providers\BlocksProvider;
use SmoothRestaurant\Providers\CartProvider;
use SmoothRestaurant\Providers\CheckoutProvider;
use SmoothRestaurant\Providers\CoreProvider;
use SmoothRestaurant\Providers\DatabaseProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\NotificationsProvider;
use SmoothRestaurant\Providers\OrdersProvider;
use SmoothRestaurant\Providers\PaymentsProvider;
use SmoothRestaurant\Providers\ReservationsProvider;
use SmoothRestaurant\Providers\RestProvider;
use SmoothRestaurant\Providers\SlotsProvider;
use SmoothRestaurant\Providers\TablesProvider;

/**
 * Class Plugin
 *
 * Main plugin singleton that bootstraps all functionality.
 */
final class Plugin
{
    /**
     * Minimum provider version accepted through the provider filter.
     *
     * Providers declaring a lower `ServiceProvider::VERSION` are skipped
     * and logged. This keeps Free and Pro on a lockstep SemVer contract.
     *
     * @var string
     */
    private const PROVIDER_VERSION_FLOOR = '0.1.0';

    /**
     * Plugin instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Service container.
     *
     * @var Container
     */
    private Container $container;

    /**
     * Provider entries skipped during registration, keyed by class with reasons.
     *
     * @var array<string, string>
     */
    private array $skippedProviders = array();

    /**
     * Private constructor.
     */
    private function __construct()
    {
        $this->container = new Container();
    }

    /**
     * Get the plugin instance.
     *
     * @return self
     */
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Reset the plugin singleton.
     *
     * @internal For unit tests only. Production code MUST NOT call this.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Boot the plugin.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerProviders();
        $this->container->boot();
    }

    /**
     * Register all service providers.
     *
     * Builds the Free provider list, passes it through the
     * `smooth_service_providers` filter so Pro can append additive providers,
     * then validates every entry. Invalid entries are ignored and logged;
     * removal or reordering of Free providers via the filter is ignored and
     * the canonical Free list is restored.
     *
     * @return void
     */
    private function registerProviders(): void
    {
        $free = $this->freeProviders();

        /**
         * Filters the service provider list.
         *
         * Pro MUST be additive only: append new providers, never remove or
         * replace Free ones. Each entry MUST be a class-string of a
         * `ServiceProvider` subclass at or above the version floor.
         *
         * @since 0.1.0
         *
         * @param array<int, class-string<ServiceProvider>> $providers Free provider list.
         * @param Container                                 $container The DI container.
         * @return array<int, class-string<ServiceProvider>> Final provider list.
         *
         * @example
         * add_filter(
         *     'smooth_service_providers',
         *     static function ( array $providers ): array {
         *         $providers[] = MyProProvider::class;
         *         return $providers;
         *     }
         * );
         */
        $filtered = apply_filters('smooth_service_providers', $free, $this->container);

        if (! is_array($filtered)) {
            $reason   = 'filter-must-return-array: falling back to the Free provider list.';
            $this->logSkipped('smooth_service_providers', $reason);
            $filtered = $free;
        }

        foreach ($free as $class) {
            if (! in_array($class, $filtered, true)) {
                $this->logSkipped($class, 'free-removal-ignored: Free providers cannot be removed via the filter.');
            }
        }

        $final = $free;
        foreach ($filtered as $candidate) {
            if (in_array($candidate, $free, true)) {
                continue;
            }

            if ($this->isValidProvider($candidate)) {
                if (! in_array($candidate, $final, true)) {
                    $final[] = $candidate;
                }
            }
        }

        foreach ($final as $providerClass) {
            if ($this->isValidProvider($providerClass, true)) {
                $this->container->register($providerClass);
            }
        }
    }

    /**
     * Get the canonical Free provider list in boot order.
     *
     * Database/Assets providers are owned by the platform stream and may not
     * exist yet in isolation; missing entries are skipped and logged, never fatal.
     *
     * @return array<int, class-string<ServiceProvider>>
     */
    private function freeProviders(): array
    {
        return array(
            CoreProvider::class,
            DatabaseProvider::class,
            AssetsProvider::class,
            MenuProvider::class,
            CartProvider::class,
            CheckoutProvider::class,
            OrdersProvider::class,
            PaymentsProvider::class,
            SlotsProvider::class,
            ReservationsProvider::class,
            TablesProvider::class,
            NotificationsProvider::class,
            AdminProvider::class,
            RestProvider::class,
            BlocksProvider::class,
        );
    }

    /**
     * Validate a single provider entry.
     *
     * @param mixed $candidate      The entry to validate.
     * @param bool  $alreadyLogged  Whether invalid entries were already logged for this pass.
     * @return bool
     */
    private function isValidProvider(mixed $candidate, bool $alreadyLogged = false): bool
    {
        if (! is_string($candidate)) {
            if (! $alreadyLogged) {
                $reason = 'unknown-class: provider entries must be class-strings.';
                $this->logSkipped('(non-string:' . gettype($candidate) . ')', $reason);
            }

            return false;
        }

        if (! class_exists($candidate)) {
            $this->logSkipped($candidate, 'unknown-class: provider class does not exist.');

            return false;
        }

        if (! is_subclass_of($candidate, ServiceProvider::class)) {
            $this->logSkipped($candidate, 'invalid-type: provider must extend ServiceProvider.');

            return false;
        }

        $version = $candidate::VERSION;
        // @phpstan-ignore-next-line Runtime guard: Pro may override the untyped VERSION const with a non-string.
        $below = ! is_string($version) || version_compare($version, self::PROVIDER_VERSION_FLOOR, '<');
        if ($below) {
            $reason = 'version-floor: provider version is below ' . self::PROVIDER_VERSION_FLOOR . '.';
            $this->logSkipped($candidate, $reason);

            return false;
        }

        return true;
    }

    /**
     * Record a skipped provider and write it to the error log.
     *
     * @param string $key    Provider class (or label for non-class entries).
     * @param string $reason Machine-readable reason for skipping.
     * @return void
     */
    private function logSkipped(string $key, string $reason): void
    {
        $this->skippedProviders[ $key ] = $reason;
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.ErrorLog -- intentional runtime log for invalid Pro providers.
        error_log(sprintf('[Smooth Restaurant] smooth_service_providers: skipping %s: %s', $key, $reason));
    }

    /**
     * Get providers skipped during registration, keyed by class with reasons.
     *
     * @return array<string, string>
     */
    public function skippedProviders(): array
    {
        return $this->skippedProviders;
    }

    /**
     * Get the class names of registered providers, in registration order.
     *
     * @return array<int, class-string<ServiceProvider>>
     */
    public function providerClasses(): array
    {
        return $this->container->providerClasses();
    }

    /**
     * Get the service container.
     *
     * @return Container
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Prevent cloning.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization.
     *
     * @throws \Exception Always throws.
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
