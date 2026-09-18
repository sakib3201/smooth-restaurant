<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\MigrationRunner;

/**
 * Unit tests for the menu-table migration at version 0.2.0.
 *
 * The 0.2.0 closure creates all three menu tables; dbDelta is captured by
 * the unit stub so the test asserts the exact statements without WordPress.
 */
final class MenuMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        sr_test_reset_stubs();
    }

    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        parent::tearDown();
    }

    public function test_target_version_is_0_2_0(): void
    {
        $this->assertSame('0.2.0', MigrationRunner::TARGET_VERSION);
    }

    public function test_defaults_registers_exactly_0_2_0(): void
    {
        $this->assertSame(['0.2.0'], array_keys(MigrationRunner::defaults()));
    }

    public function test_0_2_0_is_pending_from_0_1_0(): void
    {
        $runner = new MigrationRunner(
            MigrationRunner::defaults(),
            static fn (): string => '0.1.0',
            static function (string $version): void {
            }
        );

        $this->assertArrayHasKey('0.2.0', $runner->pending());
    }

    public function test_0_2_0_creates_all_three_tables_and_stamps_version(): void
    {
        $stored = '0.1.0';
        $runner = new MigrationRunner(
            MigrationRunner::defaults(),
            static function () use (&$stored): string {
                return $stored;
            },
            static function (string $version) use (&$stored): void {
                $stored = $version;
            }
        );

        $result = $runner->migrate();

        $this->assertSame('0.2.0', $result);
        $this->assertSame('0.2.0', $stored);

        $statements = $GLOBALS['__sr_test_dbdelta'] ?? [];
        $this->assertIsArray($statements);
        $this->assertCount(3, $statements);
        $combined = implode("\n", $statements);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_menus', $combined);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_menu_items', $combined);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_modifiers', $combined);
    }

    public function test_0_2_0_rerun_is_a_noop(): void
    {
        $stored = '0.1.0';
        $runner = new MigrationRunner(
            MigrationRunner::defaults(),
            static function () use (&$stored): string {
                return $stored;
            },
            static function (string $version) use (&$stored): void {
                $stored = $version;
            }
        );

        $runner->migrate();
        $runner->migrate();

        $statements = $GLOBALS['__sr_test_dbdelta'] ?? [];
        $this->assertIsArray($statements);
        $this->assertCount(3, $statements);
        $this->assertSame('0.2.0', $stored);
    }
}
