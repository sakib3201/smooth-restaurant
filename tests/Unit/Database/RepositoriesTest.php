<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\BaseRepository;
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
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Unit tests for the per-table repository shells.
 *
 * Every transactional table gets a thin repository over BaseRepository;
 * domain query methods land in follow-up issues.
 */
final class RepositoriesTest extends TestCase
{
    /**
     * @return array<string, array{class-string<BaseRepository>, string}>
     */
    public static function repositoryProvider(): array
    {
        return [
            'orders' => [OrderRepository::class, 'smooth_orders'],
            'order items' => [OrderItemRepository::class, 'smooth_order_items'],
            'transactions' => [TransactionRepository::class, 'smooth_transactions'],
            'reservations' => [ReservationRepository::class, 'smooth_reservations'],
            'tables' => [RestaurantTableRepository::class, 'smooth_tables'],
            'table sessions' => [TableSessionRepository::class, 'smooth_table_sessions'],
            'carts' => [CartRepository::class, 'smooth_carts'],
            'menus' => [MenuRepository::class, 'smooth_menus'],
            'menu items' => [MenuItemRepository::class, 'smooth_menu_items'],
            'modifiers' => [ModifierRepository::class, 'smooth_modifiers'],
            'coupons' => [CouponRepository::class, 'smooth_coupons'],
            'notifications' => [NotificationRepository::class, 'smooth_notifications'],
        ];
    }

    /**
     * @param class-string<BaseRepository> $class
     */
    #[DataProvider('repositoryProvider')]
    public function test_table_uses_site_prefix(string $class, string $suffix): void
    {
        $db = new FakeWpdb();
        $db->prefix = 'test_';
        $repository = new $class($db);

        $this->assertSame('test_' . $suffix, $repository->getTable());
    }

    /**
     * @param class-string<BaseRepository> $class
     */
    #[DataProvider('repositoryProvider')]
    public function test_schema_is_deltadb_first_create_table(string $class): void
    {
        $repository = new $class(new FakeWpdb());

        $schema = $repository->schema();

        $this->assertStringContainsString('CREATE TABLE ' . $repository->getTable(), $schema);
        $this->assertStringContainsString('PRIMARY KEY', $schema);
        $this->assertStringContainsString('utf8mb4', $schema);
    }

    /**
     * @param class-string<BaseRepository> $class
     */
    #[DataProvider('repositoryProvider')]
    public function test_schema_has_no_zero_date_defaults(string $class): void
    {
        $repository = new $class(new FakeWpdb());

        $this->assertStringNotContainsString(
            '0000-00-00',
            $repository->schema(),
            sprintf('%s must not use zero-date defaults (strict-mode MySQL rejects NO_ZERO_DATE).', $class)
        );
    }

    public function test_menus_schema_has_unique_slug_and_status_sort_key(): void
    {
        $schema = (new MenuRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('UNIQUE KEY slug (slug)', $schema);
        $this->assertStringContainsString('KEY status_sort (status, sort_order)', $schema);
    }

    public function test_menu_items_schema_has_menu_status_sort_composite_key(): void
    {
        $schema = (new MenuItemRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('KEY menu_status_sort (menu_id, status, sort_order)', $schema);
    }

    public function test_modifiers_schema_has_item_sort_composite_key(): void
    {
        $schema = (new ModifierRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('KEY item_sort (item_id, sort_order)', $schema);
    }

    public function test_orders_schema_has_status_created_key(): void
    {
        $schema = (new OrderRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('KEY status_created (status, created_at)', $schema);
    }

    public function test_reservations_schema_has_status_date_composite_key(): void
    {
        $schema = (new ReservationRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('KEY status_reserved (status, reserved_for)', $schema);
    }

    public function test_carts_schema_keeps_unique_session_key(): void
    {
        $schema = (new CartRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('UNIQUE KEY session_key (session_key)', $schema);
    }

    public function test_notifications_schema_has_status_date_key(): void
    {
        $schema = (new NotificationRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('KEY status_next_try (status, next_try)', $schema);
    }
}
