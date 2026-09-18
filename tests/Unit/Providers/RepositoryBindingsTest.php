<?php

/**
 * Unit tests for repository interface bindings.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Contracts\CartRepositoryInterface;
use SmoothRestaurant\Contracts\CouponRepositoryInterface;
use SmoothRestaurant\Contracts\MenuItemRepositoryInterface;
use SmoothRestaurant\Contracts\MenuRepositoryInterface;
use SmoothRestaurant\Contracts\ModifierRepositoryInterface;
use SmoothRestaurant\Contracts\NotificationRepositoryInterface;
use SmoothRestaurant\Contracts\OrderItemRepositoryInterface;
use SmoothRestaurant\Contracts\OrderRepositoryInterface;
use SmoothRestaurant\Contracts\ReservationRepositoryInterface;
use SmoothRestaurant\Contracts\RestaurantTableRepositoryInterface;
use SmoothRestaurant\Contracts\TableSessionRepositoryInterface;
use SmoothRestaurant\Contracts\TransactionRepositoryInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Database\Repositories\CartRepository;
use SmoothRestaurant\Database\Repositories\CouponRepository;
use SmoothRestaurant\Database\Repositories\NotificationRepository;
use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;
use SmoothRestaurant\Database\Repositories\OrderItemRepository;
use SmoothRestaurant\Database\Repositories\OrderRepository;
use SmoothRestaurant\Database\Repositories\ReservationRepository;
use SmoothRestaurant\Database\Repositories\RestaurantTableRepository;
use SmoothRestaurant\Database\Repositories\TableSessionRepository;
use SmoothRestaurant\Database\Repositories\TransactionRepository;
use SmoothRestaurant\Providers\CartProvider;
use SmoothRestaurant\Providers\CheckoutProvider;
use SmoothRestaurant\Providers\MenuProvider;
use SmoothRestaurant\Providers\NotificationsProvider;
use SmoothRestaurant\Providers\OrdersProvider;
use SmoothRestaurant\Providers\PaymentsProvider;
use SmoothRestaurant\Providers\ReservationsProvider;
use SmoothRestaurant\Providers\TablesProvider;

/**
 * Class RepositoryBindingsTest
 *
 * Each owning domain provider binds its repository interfaces to the
 * concrete implementations as shared singletons, so Pro can decorate or
 * replace them via `Container::instance()`.
 */
class RepositoryBindingsTest extends TestCase
{
    /**
     * Test that every repository interface resolves to its concrete singleton.
     *
     * @return void
     */
    public function test_interfaces_resolve_to_concrete_singletons(): void
    {
        $map = array(
            array( CartProvider::class, CartRepositoryInterface::class, CartRepository::class ),
            array( MenuProvider::class, MenuRepositoryInterface::class, MenuRepository::class ),
            array( MenuProvider::class, MenuItemRepositoryInterface::class, MenuItemRepository::class ),
            array( MenuProvider::class, ModifierRepositoryInterface::class, ModifierRepository::class ),
            array( CheckoutProvider::class, CouponRepositoryInterface::class, CouponRepository::class ),
            array( OrdersProvider::class, OrderRepositoryInterface::class, OrderRepository::class ),
            array( OrdersProvider::class, OrderItemRepositoryInterface::class, OrderItemRepository::class ),
            array( PaymentsProvider::class, TransactionRepositoryInterface::class, TransactionRepository::class ),
            array( ReservationsProvider::class, ReservationRepositoryInterface::class, ReservationRepository::class ),
            array( TablesProvider::class, RestaurantTableRepositoryInterface::class, RestaurantTableRepository::class ),
            array( TablesProvider::class, TableSessionRepositoryInterface::class, TableSessionRepository::class ),
            array(
                NotificationsProvider::class,
                NotificationRepositoryInterface::class,
                NotificationRepository::class
            ),
        );

        foreach ($map as [ $provider, $interface, $concrete ]) {
            $container = new Container();
            $container->register($provider);

            $this->assertTrue($container->has($interface), $interface . ' is not bound.');
            $resolved = $container->make($interface);
            $this->assertInstanceOf($concrete, $resolved, $interface . ' did not resolve to ' . $concrete . '.');
            $this->assertSame($resolved, $container->make($interface), $interface . ' is not shared.');
        }
    }
}
