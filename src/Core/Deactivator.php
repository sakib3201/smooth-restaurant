<?php

/**
 * Plugin deactivation handler.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class Deactivator
 *
 * Handles plugin deactivation tasks. Preserves all data.
 */
class Deactivator
{
    /**
     * Deactivate the plugin.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
