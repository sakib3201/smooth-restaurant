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

if ( ! isset( $GLOBALS['__sr_test_flags'] ) || ! is_array( $GLOBALS['__sr_test_flags'] ) ) {
	$GLOBALS['__sr_test_flags'] = array(
		'is_admin'   => false,
		'doing_cron' => false,
	);
}

if ( ! isset( $GLOBALS['__sr_test_hooks'] ) || ! is_array( $GLOBALS['__sr_test_hooks'] ) ) {
	$GLOBALS['__sr_test_hooks'] = array(
		'actions' => array(),
		'filters' => array(),
	);
}

if ( ! isset( $GLOBALS['__sr_test_enqueues'] ) || ! is_array( $GLOBALS['__sr_test_enqueues'] ) ) {
	$GLOBALS['__sr_test_enqueues'] = array();
}

if ( ! function_exists( 'sr_test_reset_stubs' ) ) {
	/**
	 * Reset hook storage, enqueue log, and context flags between tests.
	 *
	 * @return void
	 */
	function sr_test_reset_stubs(): void {
		$GLOBALS['__sr_test_hooks']    = array(
			'actions' => array(),
			'filters' => array(),
		);
		$GLOBALS['__sr_test_enqueues'] = array();
		$GLOBALS['__sr_test_flags']    = array(
			'is_admin'   => false,
			'doing_cron' => false,
		);
	}
}

if ( ! function_exists( 'sr_test_set_flag' ) ) {
	/**
	 * Set a context flag consumed by the conditional stubs.
	 *
	 * @param string $flag  Flag name ('is_admin' or 'doing_cron').
	 * @param bool   $value Flag value.
	 * @return void
	 */
	function sr_test_set_flag( string $flag, bool $value ): void {
		$GLOBALS['__sr_test_flags'][ $flag ] = $value;
	}
}

if ( ! function_exists( 'sr_test_add_hook' ) ) {
	/**
	 * Store a hook callback in the stub registry.
	 *
	 * @param array<string, mixed> $hooks    Registry slice (actions or filters).
	 * @param string               $hook     Hook name.
	 * @param callable             $callback Callback.
	 * @param int                  $priority Priority.
	 * @return void
	 */
	function sr_test_add_hook( array &$hooks, string $hook, callable $callback, int $priority ): void {
		$hooks[ $hook ][] = array(
			'callback' => $callback,
			'priority' => $priority,
		);
		usort(
			$hooks[ $hook ],
			static fn ( array $a, array $b ): int => $a['priority'] <=> $b['priority']
		);
	}
}

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Stub for add_action().
	 *
	 * @param string   $hook     Hook name.
	 * @param callable $callback Callback.
	 * @param int      $priority Priority.
	 * @return void
	 */
	function add_action( string $hook, callable $callback, int $priority = 10 ): void {
		sr_test_add_hook( $GLOBALS['__sr_test_hooks']['actions'], $hook, $callback, $priority );
	}
}

if ( ! function_exists( 'has_action' ) ) {
	/**
	 * Stub for has_action().
	 *
	 * @param string $hook Hook name.
	 * @return int|false Priority of the first callback, or false.
	 */
	function has_action( string $hook ): int|false {
		if ( empty( $GLOBALS['__sr_test_hooks']['actions'][ $hook ] ) ) {
			return false;
		}

		return $GLOBALS['__sr_test_hooks']['actions'][ $hook ][0]['priority'];
	}
}

if ( ! function_exists( 'remove_action' ) ) {
	/**
	 * Stub for remove_action().
	 *
	 * @param string $hook Hook name.
	 * @return bool
	 */
	function remove_action( string $hook ): bool {
		unset( $GLOBALS['__sr_test_hooks']['actions'][ $hook ] );

		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Stub for add_filter().
	 *
	 * @param string   $hook     Hook name.
	 * @param callable $callback Callback.
	 * @param int      $priority Priority.
	 * @return void
	 */
	function add_filter( string $hook, callable $callback, int $priority = 10 ): void {
		sr_test_add_hook( $GLOBALS['__sr_test_hooks']['filters'], $hook, $callback, $priority );
	}
}

if ( ! function_exists( 'has_filter' ) ) {
	/**
	 * Stub for has_filter().
	 *
	 * @param string $hook Hook name.
	 * @return int|false Priority of the first callback, or false.
	 */
	function has_filter( string $hook ): int|false {
		if ( empty( $GLOBALS['__sr_test_hooks']['filters'][ $hook ] ) ) {
			return false;
		}

		return $GLOBALS['__sr_test_hooks']['filters'][ $hook ][0]['priority'];
	}
}

if ( ! function_exists( 'remove_filter' ) ) {
	/**
	 * Stub for remove_filter().
	 *
	 * @param string $hook Hook name.
	 * @return bool
	 */
	function remove_filter( string $hook ): bool {
		unset( $GLOBALS['__sr_test_hooks']['filters'][ $hook ] );

		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Stub for apply_filters(): threads the value through registered callbacks.
	 *
	 * @param string $hook  Hook name.
	 * @param mixed  $value Filtered value.
	 * @param mixed  ...$args Additional arguments.
	 * @return mixed
	 */
	function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
		foreach ( $GLOBALS['__sr_test_hooks']['filters'][ $hook ] ?? array() as $entry ) {
			$value = ( $entry['callback'] )( $value, ...$args );
		}

		return $value;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	/**
	 * Stub for do_action(): invokes registered callbacks.
	 *
	 * @param string $hook Hook name.
	 * @param mixed  ...$args Action arguments.
	 * @return void
	 */
	function do_action( string $hook, mixed ...$args ): void {
		foreach ( $GLOBALS['__sr_test_hooks']['actions'][ $hook ] ?? array() as $entry ) {
			( $entry['callback'] )( ...$args );
		}
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Stub for is_admin(), driven by the 'is_admin' test flag.
	 *
	 * @return bool
	 */
	function is_admin(): bool {
		return (bool) ( $GLOBALS['__sr_test_flags']['is_admin'] ?? false );
	}
}

if ( ! function_exists( 'wp_doing_cron' ) ) {
	/**
	 * Stub for wp_doing_cron(), driven by the 'doing_cron' test flag.
	 *
	 * @return bool
	 */
	function wp_doing_cron(): bool {
		return (bool) ( $GLOBALS['__sr_test_flags']['doing_cron'] ?? false );
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	/**
	 * Stub for wp_enqueue_script(): records the handle instead of printing.
	 *
	 * @param string $handle Script handle.
	 * @return void
	 */
	function wp_enqueue_script( string $handle ): void {
		$GLOBALS['__sr_test_enqueues']['scripts'][] = $handle;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	/**
	 * Stub for wp_enqueue_style(): records the handle instead of printing.
	 *
	 * @param string $handle Style handle.
	 * @return void
	 */
	function wp_enqueue_style( string $handle ): void {
		$GLOBALS['__sr_test_enqueues']['styles'][] = $handle;
	}
}
