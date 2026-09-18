<?php

/**
 * Plugin activation handler.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

use SmoothRestaurant\Database\MigrationRunner;

/**
 * Class Activator
 *
 * Handles plugin activation tasks. Runs pending database migrations
 * (network-wide when requested) and flushes rewrite rules. Roles and
 * options land in follow-up issues.
 */
class Activator
{
    /**
     * Activate the plugin.
     *
     * @param bool $network_wide Whether the plugin is network-activated.
     * @return void
     */
    public static function activate(bool $network_wide = false): void
    {
        $runner = new MigrationRunner(MigrationRunner::defaults());
        $runner->migrateAll($network_wide);
        self::flushRewrites();
    }

    /**
     * Flush rewrite rules when the WordPress API is available.
     *
     * @return void
     */
    private static function flushRewrites(): void
    {
        if (function_exists(__NAMESPACE__ . '\\flush_rewrite_rules') || function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules();
        }
    }
}
