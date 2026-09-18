<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\MigrationRunner;

/**
 * Unit tests for the MigrationRunner contract.
 *
 * The runner owns the canonical smooth_db_version option, applies
 * version-guarded idempotent migrations in order, and loops all sites on
 * network-wide activation. No domain tables ship in this change; the
 * migrations under test are synthetic fixtures exercising the contract.
 */
final class MigrationRunnerTest extends TestCase
{
    private string $stored = '0.0.0';

    protected function setUp(): void
    {
        parent::setUp();
        $this->stored = '0.0.0';
    }

    public function test_option_key_is_canonical(): void
    {
        $this->assertSame('smooth_db_version', MigrationRunner::OPTION);
    }

    public function test_migrate_runs_pending_migrations_in_version_order(): void
    {
        $order = [];
        $runner = $this->runner();
        $runner->register('0.0.2', function () use (&$order): void {
            $order[] = '0.0.2';
        });
        $runner->register('0.0.1', function () use (&$order): void {
            $order[] = '0.0.1';
        });

        $result = $runner->migrate();

        $this->assertSame(['0.0.1', '0.0.2'], $order);
        $this->assertSame(MigrationRunner::TARGET_VERSION, $result);
        $this->assertSame(MigrationRunner::TARGET_VERSION, $this->stored);
    }

    public function test_rerun_is_a_noop(): void
    {
        $runs = 0;
        $runner = $this->runner();
        $runner->register('0.0.1', function () use (&$runs): void {
            $runs++;
        });

        $runner->migrate();
        $storedAfterFirst = $this->stored;
        $runner->migrate();

        $this->assertSame(1, $runs);
        $this->assertSame($storedAfterFirst, $this->stored);
    }

    public function test_skips_migrations_at_or_below_stored_version(): void
    {
        $this->stored = '0.0.2';
        $ran = [];
        $runner = $this->runner();
        $runner->register('0.0.1', function () use (&$ran): void {
            $ran[] = '0.0.1';
        });
        $runner->register('0.0.3', function () use (&$ran): void {
            $ran[] = '0.0.3';
        });

        $runner->migrate();

        $this->assertSame(['0.0.3'], $ran);
    }

    public function test_ignores_migrations_above_target_version(): void
    {
        $ran = false;
        $runner = $this->runner();
        $runner->register('9.9.9', function () use (&$ran): void {
            $ran = true;
        });

        $result = $runner->migrate();

        $this->assertFalse($ran);
        $this->assertSame(MigrationRunner::TARGET_VERSION, $result);
    }

    public function test_does_not_downgrade_newer_stored_version(): void
    {
        $this->stored = '9.9.9';
        $runner = $this->runner();

        $result = $runner->migrate();

        $this->assertSame('9.9.9', $result);
        $this->assertSame('9.9.9', $this->stored);
    }

    public function test_network_activation_covers_all_sites(): void
    {
        $runner = new FakeNetworkMigrationRunner();

        $runner->migrateAll(true);

        $this->assertSame([1, 2], $runner->visited);
        $this->assertSame(MigrationRunner::TARGET_VERSION, $runner->versions[1]);
        $this->assertSame(MigrationRunner::TARGET_VERSION, $runner->versions[2]);
    }

    public function test_single_site_run_without_multisite_api(): void
    {
        $runner = $this->runner();

        $runner->migrateAll(true);

        $this->assertSame(MigrationRunner::TARGET_VERSION, $this->stored);
    }

    /**
     * @param array<string, callable(): void> $migrations
     */
    private function runner(array $migrations = []): MigrationRunner
    {
        return new MigrationRunner(
            $migrations,
            function (): string {
                return $this->stored;
            },
            function (string $version): void {
                $this->stored = $version;
            }
        );
    }
}

/**
 * MigrationRunner with a scripted multisite environment.
 *
 * Overrides the multisite seams so the network-wide loop is testable
 * without WordPress.
 */
class FakeNetworkMigrationRunner extends MigrationRunner
{
    /** @var array<int, string> */
    public array $versions = [1 => '0.0.0', 2 => '0.0.0'];

    public int $currentSite = 1;

    /** @var list<int> */
    public array $visited = [];

    public function __construct()
    {
        parent::__construct(
            [],
            function (): string {
                return $this->versions[$this->currentSite];
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
        return [1, 2];
    }

    protected function switchToBlog(int $siteId): void
    {
        $this->currentSite = $siteId;
        $this->visited[] = $siteId;
    }

    protected function restoreBlog(): void
    {
    }
}
