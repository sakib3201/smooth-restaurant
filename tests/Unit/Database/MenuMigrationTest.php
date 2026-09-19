<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\MigrationRunner;

/**
 * Unit tests for the menu-table migrations at versions 0.2.0 and 0.3.0.
 *
 * The 0.2.0 closure creates all three menu tables; the 0.3.0 closure re-runs
 * all three createTable() calls so dbDelta converges the additive modifier
 * status column (existing rows backfill to the publish default). dbDelta is
 * captured by the unit stub so the tests assert the exact statements without
 * WordPress.
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

    public function test_target_version_is_0_3_0(): void
    {
        $this->assertSame('0.3.0', MigrationRunner::TARGET_VERSION);
    }

    public function test_defaults_registers_0_2_0_and_0_3_0(): void
    {
        $this->assertSame(['0.2.0', '0.3.0'], array_keys(MigrationRunner::defaults()));
    }

    public function test_0_3_0_is_pending_from_0_2_0(): void
    {
        $runner = new MigrationRunner(
            MigrationRunner::defaults(),
            static fn (): string => '0.2.0',
            static function (string $version): void {
            }
        );

        $this->assertArrayHasKey('0.3.0', $runner->pending());
    }

    public function test_full_migrate_runs_both_steps_and_stamps_0_3_0(): void
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

        $this->assertSame('0.3.0', $result);
        $this->assertSame('0.3.0', $stored);

        $statements = $GLOBALS['__sr_test_dbdelta'] ?? [];
        $this->assertIsArray($statements);
        $this->assertCount(6, $statements);
        $combined = implode("\n", $statements);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_menus', $combined);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_menu_items', $combined);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_modifiers', $combined);
    }

    public function test_0_3_0_rerun_converges_the_status_column(): void
    {
        $stored = '0.2.0';
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

        $this->assertSame('0.3.0', $result);
        $this->assertSame('0.3.0', $stored);

        $statements = $GLOBALS['__sr_test_dbdelta'] ?? [];
        $this->assertIsArray($statements);
        $this->assertCount(3, $statements);
        $combined = implode("\n", $statements);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_menus', $combined);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_menu_items', $combined);
        $this->assertStringContainsString('CREATE TABLE wp_smooth_modifiers', $combined);
        $this->assertStringContainsString("status varchar(32) NOT NULL DEFAULT 'publish'", $combined);
    }

    public function test_0_3_0_rerun_is_a_noop(): void
    {
        $stored = '0.2.0';
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
        $this->assertSame('0.3.0', $stored);
    }

    public function test_network_migrate_stamps_0_3_0_on_every_site(): void
    {
        $runner = new FakeMenuNetworkMigrationRunner();

        $runner->migrateAll(true);

        $this->assertSame('0.3.0', $runner->versions[1]);
        $this->assertSame('0.3.0', $runner->versions[2]);
    }
}

/**
 * MigrationRunner with a scripted two-site network at version 0.2.0.
 *
 * Overrides the multisite seams so the 0.3.0 network-wide stamp is testable
 * without WordPress.
 */
class FakeMenuNetworkMigrationRunner extends MigrationRunner
{
    /** @var array<int, string> */
    public array $versions = [1 => '0.2.0', 2 => '0.2.0'];

    /** @var list<int> */
    public array $sites = [1, 2];

    /** @var list<int> */
    public array $pending = [];

    public int $currentSite = 1;

    public function __construct()
    {
        parent::__construct(
            MigrationRunner::defaults(),
            function (): string {
                return $this->versions[$this->currentSite] ?? '0.0.0';
            },
            function (string $version): void {
                $this->versions[$this->currentSite] = $version;
            }
        );
    }

    protected function isMultisite(): bool
    {
        return true;
    }

    /**
     * @return list<int>
     */
    protected function siteIds(): array
    {
        return $this->sites;
    }

    /**
     * @return list<int>
     */
    protected function pendingSiteIds(): array
    {
        return $this->pending;
    }

    /**
     * @param list<int> $siteIds
     */
    protected function storePendingSiteIds(array $siteIds): void
    {
        $this->pending = array_values($siteIds);
    }

    protected function switchToBlog(int $siteId): void
    {
        $this->currentSite = $siteId;
    }

    protected function restoreBlog(): void
    {
    }
}
