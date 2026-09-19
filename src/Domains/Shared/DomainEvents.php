<?php

/**
 * Domain event contracts.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Shared;

/**
 * Class DomainEvents
 *
 * Canonical domain-event names plus a guarded dispatcher. Domain services
 * are still shells, so no call sites exist yet: follow-up domain issues
 * dispatch these at their state transitions (e.g. order persistence fires
 * `ORDER_CREATED`). Pro and addons subscribe without overriding providers.
 */
final class DomainEvents
{
    /**
     * Fired after an order row is persisted.
     */
    public const ORDER_CREATED = 'smooth_restaurant_order_created';

    /**
     * Fired after a payment capture is recorded in the ledger.
     */
    public const PAYMENT_CAPTURED = 'smooth_restaurant_payment_captured';

    /**
     * Fired after a refund row is appended to the ledger.
     */
    public const PAYMENT_REFUNDED = 'smooth_restaurant_payment_refunded';

    /**
     * Fired after a reservation is confirmed.
     */
    public const RESERVATION_CONFIRMED = 'smooth_restaurant_reservation_confirmed';

    /**
     * Fired after a cart draft row is created or updated.
     */
    public const CART_UPDATED = 'smooth_restaurant_cart_updated';

    /**
     * Fired after a menu row is created or updated.
     */
    public const MENU_SAVED = 'smooth_restaurant_menu_saved';

    /**
     * Fired after a menu item row is created, updated, deleted, or reordered.
     */
    public const MENU_ITEM_SAVED = 'smooth_restaurant_menu_item_saved';

    /**
     * Fired after a modifier row is created, updated, deleted, or reordered.
     */
    public const MODIFIER_SAVED = 'smooth_restaurant_modifier_saved';

    /**
     * Dispatch a domain event.
     *
     * No-op when WordPress is not loaded (unit-test context).
     *
     * @param string               $event   One of the self::* event names.
     * @param array<string, mixed> $payload Event payload.
     * @return void
     */
    public static function dispatch(string $event, array $payload = array()): void
    {
        if (! function_exists('do_action')) {
            return;
        }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- event names are the smooth_restaurant_* class constants above.
        do_action($event, $payload);
    }
}
