<?php

/**
 * Minimal WordPress function stubs for the Unit suite.
 *
 * These are defined ONLY when the real WordPress test environment is not
 * loaded, so wp-env runs keep using the real functions. The stubs model a
 * bare frontend request: no admin, no cron, no REST, no enqueued assets.
 * Tests switch contexts through `$GLOBALS['__sr_test_flags']`.
 *
 * Supported hooks subset: add_action / has_action / remove_action /
 * add_filter / has_filter / remove_filter / apply_filters / do_action.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

if (! isset($GLOBALS['__sr_test_flags']) || ! is_array($GLOBALS['__sr_test_flags'])) {
    $GLOBALS['__sr_test_flags'] = array(
        'is_admin'   => false,
        'doing_cron' => false,
    );
}

if (! isset($GLOBALS['__sr_test_hooks']) || ! is_array($GLOBALS['__sr_test_hooks'])) {
    $GLOBALS['__sr_test_hooks'] = array(
        'actions' => array(),
        'filters' => array(),
    );
}

if (! isset($GLOBALS['__sr_test_enqueues']) || ! is_array($GLOBALS['__sr_test_enqueues'])) {
    $GLOBALS['__sr_test_enqueues'] = array();
}

if (! isset($GLOBALS['__sr_test_caps']) || ! is_array($GLOBALS['__sr_test_caps'])) {
    $GLOBALS['__sr_test_caps'] = array();
}

if (! isset($GLOBALS['__sr_test_routes']) || ! is_array($GLOBALS['__sr_test_routes'])) {
    $GLOBALS['__sr_test_routes'] = array();
}

if (! isset($GLOBALS['__sr_test_bindings']) || ! is_array($GLOBALS['__sr_test_bindings'])) {
    $GLOBALS['__sr_test_bindings'] = array();
}

if (! isset($GLOBALS['__sr_test_cache']) || ! is_array($GLOBALS['__sr_test_cache'])) {
    $GLOBALS['__sr_test_cache'] = array();
}

if (! isset($GLOBALS['__sr_test_dbdelta']) || ! is_array($GLOBALS['__sr_test_dbdelta'])) {
    $GLOBALS['__sr_test_dbdelta'] = array();
}

if (! function_exists('sr_test_reset_stubs')) {
    /**
     * Reset hook storage, enqueue log, and context flags between tests.
     *
     * @return void
     */
    function sr_test_reset_stubs(): void
    {
        $GLOBALS['__sr_test_hooks']    = array(
            'actions' => array(),
            'filters' => array(),
        );
        $GLOBALS['__sr_test_enqueues'] = array();
        $GLOBALS['__sr_test_caps']     = array();
        $GLOBALS['__sr_test_routes']   = array();
        $GLOBALS['__sr_test_bindings'] = array();
        $GLOBALS['__sr_test_cache']    = array();
        $GLOBALS['__sr_test_dbdelta']  = array();
        $GLOBALS['__sr_test_flags']    = array(
            'is_admin'   => false,
            'doing_cron' => false,
        );
    }
}

if (! function_exists('sr_test_set_flag')) {
    /**
     * Set a context flag consumed by the conditional stubs.
     *
     * @param string $flag  Flag name ('is_admin' or 'doing_cron').
     * @param bool   $value Flag value.
     * @return void
     */
    function sr_test_set_flag(string $flag, bool $value): void
    {
        $GLOBALS['__sr_test_flags'][ $flag ] = $value;
    }
}

if (! function_exists('sr_test_grant_caps')) {
    /**
     * Grant capabilities to the stubbed current user.
     *
     * The current_user_can() stub denies everything by default (bare
     * frontend request, no authenticated user); tests opt in here.
     *
     * @param list<string> $caps Capabilities the stub user holds.
     * @return void
     */
    function sr_test_grant_caps(array $caps): void
    {
        $GLOBALS['__sr_test_caps'] = array_values($caps);
    }
}

if (! function_exists('sr_test_add_hook')) {
    /**
     * Store a hook callback in the stub registry.
     *
     * @param array<string, mixed> $hooks    Registry slice (actions or filters).
     * @param string               $hook     Hook name.
     * @param callable             $callback Callback.
     * @param int                  $priority Priority.
     * @return void
     */
    function sr_test_add_hook(array &$hooks, string $hook, callable $callback, int $priority): void
    {
        $hooks[ $hook ][] = array(
            'callback' => $callback,
            'priority' => $priority,
        );
        usort(
            $hooks[ $hook ],
            static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']
        );
    }
}

if (! function_exists('add_action')) {
    /**
     * Stub for add_action().
     *
     * @param string   $hook          Hook name.
     * @param callable $callback      Callback.
     * @param int      $priority      Priority.
     * @param int      $acceptedArgs  Accepted argument count (recorded, not enforced).
     * @return void
     */
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        sr_test_add_hook($GLOBALS['__sr_test_hooks']['actions'], $hook, $callback, $priority);
    }
}

if (! function_exists('has_action')) {
    /**
     * Stub for has_action().
     *
     * @param string $hook Hook name.
     * @return int|false Priority of the first callback, or false.
     */
    function has_action(string $hook): int|false
    {
        $actions = $GLOBALS['__sr_test_hooks']['actions'][ $hook ] ?? null;
        if (! is_array($actions) || [] === $actions) {
            return false;
        }

        return $GLOBALS['__sr_test_hooks']['actions'][ $hook ][0]['priority'];
    }
}

if (! function_exists('remove_action')) {
    /**
     * Stub for remove_action().
     *
     * @param string $hook Hook name.
     * @return bool
     */
    function remove_action(string $hook): bool
    {
        unset($GLOBALS['__sr_test_hooks']['actions'][ $hook ]);

        return true;
    }
}

if (! function_exists('add_filter')) {
    /**
     * Stub for add_filter().
     *
     * @param string   $hook          Hook name.
     * @param callable $callback      Callback.
     * @param int      $priority      Priority.
     * @param int      $acceptedArgs  Accepted argument count (recorded, not enforced).
     * @return void
     */
    function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        sr_test_add_hook($GLOBALS['__sr_test_hooks']['filters'], $hook, $callback, $priority);
    }
}

if (! function_exists('has_filter')) {
    /**
     * Stub for has_filter().
     *
     * @param string $hook Hook name.
     * @return int|false Priority of the first callback, or false.
     */
    function has_filter(string $hook): int|false
    {
        $filters = $GLOBALS['__sr_test_hooks']['filters'][ $hook ] ?? null;
        if (! is_array($filters) || [] === $filters) {
            return false;
        }

        return $GLOBALS['__sr_test_hooks']['filters'][ $hook ][0]['priority'];
    }
}

if (! function_exists('remove_filter')) {
    /**
     * Stub for remove_filter().
     *
     * @param string $hook Hook name.
     * @return bool
     */
    function remove_filter(string $hook): bool
    {
        unset($GLOBALS['__sr_test_hooks']['filters'][ $hook ]);

        return true;
    }
}

if (! function_exists('apply_filters')) {
    /**
     * Stub for apply_filters(): threads the value through registered callbacks.
     *
     * @param string $hook  Hook name.
     * @param mixed  $value Filtered value.
     * @param mixed  ...$args Additional arguments.
     * @return mixed
     */
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        foreach ($GLOBALS['__sr_test_hooks']['filters'][ $hook ] ?? array() as $entry) {
            $value = ( $entry['callback'] )($value, ...$args);
        }

        return $value;
    }
}

if (! function_exists('do_action')) {
    /**
     * Stub for do_action(): invokes registered callbacks.
     *
     * @param string $hook Hook name.
     * @param mixed  ...$args Action arguments.
     * @return void
     */
    function do_action(string $hook, mixed ...$args): void
    {
        foreach ($GLOBALS['__sr_test_hooks']['actions'][ $hook ] ?? array() as $entry) {
            ( $entry['callback'] )(...$args);
        }
    }
}

if (! function_exists('is_admin')) {
    /**
     * Stub for is_admin(), driven by the 'is_admin' test flag.
     *
     * @return bool
     */
    function is_admin(): bool
    {
        return (bool) ( $GLOBALS['__sr_test_flags']['is_admin'] ?? false );
    }
}

if (! function_exists('wp_doing_cron')) {
    /**
     * Stub for wp_doing_cron(), driven by the 'doing_cron' test flag.
     *
     * @return bool
     */
    function wp_doing_cron(): bool
    {
        return (bool) ( $GLOBALS['__sr_test_flags']['doing_cron'] ?? false );
    }
}

if (! function_exists('wp_enqueue_script')) {
    /**
     * Stub for wp_enqueue_script(): records the handle instead of printing.
     *
     * @param string $handle Script handle.
     * @return void
     */
    function wp_enqueue_script(string $handle): void
    {
        $GLOBALS['__sr_test_enqueues']['scripts'][] = $handle;
    }
}

if (! function_exists('wp_enqueue_style')) {
    /**
     * Stub for wp_enqueue_style(): records the handle instead of printing.
     *
     * @param string $handle Style handle.
     * @return void
     */
    function wp_enqueue_style(string $handle): void
    {
        $GLOBALS['__sr_test_enqueues']['styles'][] = $handle;
    }
}

if (! function_exists('current_user_can')) {
    /**
     * Stub for current_user_can(): denies everything unless the capability
     * was granted via sr_test_grant_caps().
     *
     * @param string $cap Capability being checked.
     * @return bool
     */
    function current_user_can(string $cap): bool
    {
        return \in_array($cap, $GLOBALS['__sr_test_caps'] ?? array(), true);
    }
}

if (! function_exists('register_rest_route')) {
    /**
     * Stub for register_rest_route(): records the registration for assertions.
     *
     * @param string               $namespace Route namespace.
     * @param string               $route     Route path.
     * @param array<string, mixed> $args      Route arguments.
     * @param bool                 $override  Whether to override existing routes.
     * @return bool
     */
    function register_rest_route(string $namespace, string $route, array $args = array(), bool $override = false): bool
    {
        $GLOBALS['__sr_test_routes'][] = array(
            'namespace' => $namespace,
            'route'     => $route,
            'args'      => $args,
            'override'  => $override,
        );

        return true;
    }
}

if (! function_exists('register_block_bindings_source')) {
    /**
     * Stub for register_block_bindings_source(): records the source for assertions.
     *
     * @param string               $name Source name.
     * @param array<string, mixed> $args Source arguments.
     * @return null Always null in the stub (no source object without WordPress).
     */
    function register_block_bindings_source(string $name, array $args): mixed
    {
        $GLOBALS['__sr_test_bindings'][ $name ] = $args;

        return null;
    }
}

if (! function_exists('wp_cache_get')) {
    /**
     * Stub for wp_cache_get(): reads the in-memory test cache (misses as false).
     *
     * @param string $key   Cache key.
     * @param string $group Cache group.
     * @return mixed
     */
    function wp_cache_get(string $key, string $group = ''): mixed
    {
        return $GLOBALS['__sr_test_cache'][ $group ][ $key ] ?? false;
    }
}

if (! function_exists('wp_cache_set')) {
    /**
     * Stub for wp_cache_set(): writes the in-memory test cache.
     *
     * @param string $key    Cache key.
     * @param mixed  $data   Cached value.
     * @param string $group  Cache group.
     * @param int    $expire Expiration in seconds (ignored by the stub).
     * @return bool
     */
    function wp_cache_set(string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $GLOBALS['__sr_test_cache'][ $group ][ $key ] = $data;

        return true;
    }
}

if (! function_exists('wp_cache_delete')) {
    /**
     * Stub for wp_cache_delete(): removes a key from the in-memory test cache.
     *
     * @param string $key   Cache key.
     * @param string $group Cache group.
     * @return bool Whether the key existed.
     */
    function wp_cache_delete(string $key, string $group = ''): bool
    {
        if (! isset($GLOBALS['__sr_test_cache'][ $group ][ $key ])) {
            return false;
        }
        unset($GLOBALS['__sr_test_cache'][ $group ][ $key ]);

        return true;
    }
}

if (! function_exists('get_current_blog_id')) {
    /**
     * Stub for get_current_blog_id(): single-site default without WordPress.
     *
     * @return int
     */
    function get_current_blog_id(): int
    {
        return (int) ( $GLOBALS['__sr_test_flags']['blog_id'] ?? 1 );
    }
}

if (! function_exists('dbDelta')) {
    /**
     * Stub for dbDelta(): captures schema statements for migration assertions.
     *
     * @param string $queries CREATE TABLE statement.
     * @return list<string> Empty (no deltas computed without WordPress).
     */
    function dbDelta(string $queries): array
    {
        $GLOBALS['__sr_test_dbdelta'][] = $queries;

        return array();
    }
}
