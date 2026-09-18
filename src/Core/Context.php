<?php

/**
 * Request context helper.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class Context
 *
 * Resolves the current request context so `Plugin::registerProviders()` can
 * skip providers whose `boot()` would bail before instantiating them.
 * Every check is `function_exists`-guarded (or constant-guarded), so unit
 * tests without WordPress read as the frontend default.
 *
 * Tests may force a context via `override()`; always pair with `reset()`.
 */
final class Context
{
    /**
     * Admin dashboard context.
     */
    public const ADMIN = 'admin';

    /**
     * REST API context.
     */
    public const REST = 'rest';

    /**
     * Cron context.
     */
    public const CRON = 'cron';

    /**
     * Diner frontend context (default when WordPress is not loaded).
     */
    public const FRONTEND = 'frontend';

    /**
     * WP-CLI context.
     */
    public const CLI = 'cli';

    /**
     * Forced context for tests, null in production.
     *
     * @var string|null
     */
    private static ?string $override = null;

    /**
     * Resolve the current request context.
     *
     * Precedence: cron, REST, admin, WP-CLI, frontend.
     *
     * @return string One of admin|rest|cron|frontend|cli.
     */
    public static function current(): string
    {
        if (null !== self::$override) {
            return self::$override;
        }

        if (function_exists('wp_doing_cron') && wp_doing_cron()) {
            return self::CRON;
        }

        if (defined('REST_REQUEST') && (bool) REST_REQUEST) {
            return self::REST;
        }

        if (function_exists('is_admin') && is_admin()) {
            return self::ADMIN;
        }

        if (defined('WP_CLI') && (bool) WP_CLI) {
            return self::CLI;
        }

        return self::FRONTEND;
    }

    /**
     * Force a context.
     *
     * @internal For unit tests only. Production code MUST NOT call this.
     *
     * @param string|null $context Forced context, null to clear.
     * @return void
     */
    public static function override(?string $context): void
    {
        self::$override = $context;
    }

    /**
     * Clear any forced context.
     *
     * @internal For unit tests only.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$override = null;
    }
}
