<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\Repositories\OrderRepository;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Unit tests for the shared BaseRepository behaviour.
 */
final class BaseRepositoryTest extends TestCase
{
    public function test_table_uses_site_prefix(): void
    {
        $db = new FakeWpdb();
        $db->prefix = 'test_';
        $repository = new OrderRepository($db);

        $this->assertSame('test_smooth_orders', $repository->getTable());
    }

    public function test_charset_collate_delegates_to_connection(): void
    {
        $repository = new ExposedOrderRepository(new FakeWpdb());

        $this->assertSame(
            'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            $repository->exposedCharsetCollate()
        );
    }

    public function test_prepare_delegates_to_connection(): void
    {
        $db = new FakeWpdb();
        $repository = new ExposedOrderRepository($db);

        $prepared = $repository->exposedPrepare('SELECT * FROM t WHERE id = %d', 7);

        $this->assertSame('SELECT * FROM t WHERE id = %d|7', $prepared);
        $this->assertSame(['SELECT * FROM t WHERE id = %d'], $db->queries);
    }

    public function test_map_rows_casts_typed_values(): void
    {
        $repository = new OrderRepository(new FakeWpdb());

        $mapped = $repository->mapRows([
            ['id' => '3', 'status' => 'pending', 'currency' => 'USD', 'total_cents' => '1299'],
        ]);

        $this->assertSame([
            ['id' => 3, 'status' => 'pending', 'currency' => 'USD', 'total_cents' => 1299],
        ], $mapped);
    }

    public function test_schema_embeds_prefixed_table_and_collation(): void
    {
        $repository = new OrderRepository(new FakeWpdb());

        $schema = $repository->schema();

        $this->assertStringContainsString('CREATE TABLE wp_smooth_orders', $schema);
        $this->assertStringContainsString('DEFAULT CHARACTER SET utf8mb4', $schema);
    }

    public function test_create_table_requires_wordpress_upgrade_api(): void
    {
        $repository = new OrderRepository(new FakeWpdb());

        $this->expectException(\RuntimeException::class);
        $repository->createTable();
    }
}

/**
 * OrderRepository subclass exposing protected infrastructure for tests.
 */
class ExposedOrderRepository extends OrderRepository
{
    public function exposedCharsetCollate(): string
    {
        return $this->charsetCollate();
    }

    public function exposedPrepare(string $query, mixed ...$args): string
    {
        return $this->prepare($query, ...$args);
    }
}
