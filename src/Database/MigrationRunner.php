<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database;

use SmoothRestaurant\Database\Repositories\MenuItemRepository;
use SmoothRestaurant\Database\Repositories\MenuRepository;
use SmoothRestaurant\Database\Repositories\ModifierRepository;

/**
 * Database migration runner.
 *
 * Owns the canonical smooth_db_version option. Migrations are
 * version-guarded, idempotent, and additive: each entry runs once, in
 * version order, and only when the stored version is older. Destructive
 * renames are never shipped in minors, and backfills run via background
 * jobs — never in the request path, and never through this runner beyond
 * additive schema steps.
 *
  * This runner ships the migration contract plus the multisite loop;
  * domain tables land as version-guarded entries in defaults().
 */
class MigrationRunner
{
    /**
     * Canonical database-version option key.
     */
    public const OPTION = 'smooth_db_version';

    /**
     * Cursor option holding site IDs deferred by batched network migrations.
     */
    public const PENDING_OPTION = 'smooth_migrations_pending';

    /**
     * Database schema version this plugin code understands.
     */
    public const TARGET_VERSION = '0.3.0';

    /**
     * Registered migrations keyed by version.
     *
     * @var array<string, callable(): void>
     */
    private array $migrations;

    /**
     * Reads the stored version.
     *
     * @var \Closure(): string
     */
    private \Closure $reader;

    /**
     * Persists the stored version.
     *
     * @var \Closure(string): void
     */
    private \Closure $writer;

    /**
     * @param array<string, callable(): void> $migrations Version-keyed migrations.
     * @param \Closure(): string|null $reader Version reader; defaults to the canonical option.
     * @param \Closure(string): void|null $writer Version writer; defaults to the canonical option.
     */
    public function __construct(array $migrations = [], ?\Closure $reader = null, ?\Closure $writer = null)
    {
        $this->migrations = $migrations;
        $this->reader = $reader ?? static function (): string {
            if (!\function_exists('get_option')) {
                return '0.0.0';
            }
            $value = \get_option(self::OPTION, '0.0.0');

            return \is_string($value) ? $value : '0.0.0';
        };
        $this->writer = $writer ?? static function (string $version): void {
            if (\function_exists('update_option')) {
                \update_option(self::OPTION, $version, false);
            }
        };
    }

    /**
     * Default migrations for the current plugin version.
     *
     * Follow-up domain issues append their version-guarded, idempotent,
     * additive migrations here. Version 0.2.0 creates the three menu
     * tables; each createTable() call is independently re-runnable, so a
     * partial failure retries cleanly. Version 0.3.0 re-runs all three
     * menu createTable() calls so dbDelta converges the additive modifier
     * status column; cycle 3 owns this single version bump (SMO-144).
     *
     * @return array<string, callable(): void>
     */
    public static function defaults(): array
    {
        return [
            '0.2.0' => static function (): void {
                (new MenuRepository())->createTable();
                (new MenuItemRepository())->createTable();
                (new ModifierRepository())->createTable();
            },
            '0.3.0' => static function (): void {
                (new MenuRepository())->createTable();
                (new MenuItemRepository())->createTable();
                (new ModifierRepository())->createTable();
            },
        ];
    }

    /**
     * Register one idempotent additive migration.
     *
     * @param string $version Version this migration brings the schema to.
     * @param callable(): void $migration Idempotent migration step.
     */
    public function register(string $version, callable $migration): void
    {
        $this->migrations[$version] = $migration;
    }

    /**
     * Migrations still pending for the stored version, oldest first.
     *
     * @return array<string, callable(): void>
     */
    public function pending(): array
    {
        $stored = $this->storedVersion();
        $pending = [];
        foreach ($this->migrations as $version => $migration) {
            if (\version_compare($version, $stored, '>') && \version_compare($version, self::TARGET_VERSION, '<=')) {
                $pending[$version] = $migration;
            }
        }
        \uksort($pending, static fn (string $a, string $b): int => \version_compare($a, $b));

        return $pending;
    }

    /**
     * Read the stored schema version.
     */
    public function storedVersion(): string
    {
        $version = ($this->reader)();

        return '' !== $version ? $version : '0.0.0';
    }

    /**
     * Run pending migrations in order and record the resulting version.
     *
     * Safe to rerun: a second run at the same version is a no-op. Newer
     * stored versions (downgrade-safe reads) are never rewritten.
     */
    public function migrate(): string
    {
        foreach ($this->pending() as $version => $migration) {
            $migration();
            $this->writeVersion($version);
        }
        if (\version_compare($this->storedVersion(), self::TARGET_VERSION, '<')) {
            $this->writeVersion(self::TARGET_VERSION);
        }

        return $this->storedVersion();
    }

    /**
     * Run migrations for this site, or for every site on network-wide activation.
     *
     * Each site keeps its own smooth_db_version value. Large networks are
     * processed in batches: when the site count exceeds $batchSize, the first
     * batch migrates now and the remaining site IDs are stashed in the
     * smooth_migrations_pending cursor for resumePending() (hooked to
     * admin_init by DatabaseProvider) to drain.
     *
     * @param bool $networkWide Whether to loop every site on the network.
     * @param int  $batchSize   Sites to migrate synchronously; values below 1 behave as 1.
     */
    public function migrateAll(bool $networkWide = false, int $batchSize = 100): void
    {
        if (!$networkWide || !$this->isMultisite()) {
            $this->migrate();

            return;
        }
        if ($batchSize < 1) {
            $batchSize = 1;
        }
        $siteIds = $this->siteIds();
        if (\count($siteIds) <= $batchSize) {
            foreach ($siteIds as $siteId) {
                $this->migrateSite($siteId);
            }

            return;
        }
        foreach (\array_slice($siteIds, 0, $batchSize) as $siteId) {
            $this->migrateSite($siteId);
        }
        $this->storePendingSiteIds(\array_slice($siteIds, $batchSize));
    }

    /**
     * Drain the deferred network-migration cursor.
     *
     * Migrates every stashed site ID, clears the cursor, and returns the
     * number of sites processed (0 when nothing is pending). Migrations are
     * idempotent, so a repeated or partial drain is safe to rerun.
     */
    public function resumePending(): int
    {
        $pending = $this->pendingSiteIds();
        if ([] === $pending) {
            return 0;
        }
        $processed = 0;
        foreach ($pending as $siteId) {
            $this->migrateSite($siteId);
            $processed++;
        }
        $this->storePendingSiteIds([]);

        return $processed;
    }

    /**
     * Migrate a single network site, restoring the blog context afterwards.
     */
    private function migrateSite(int $siteId): void
    {
        $this->switchToBlog($siteId);
        try {
            $this->migrate();
        } finally {
            $this->restoreBlog();
        }
    }

    /**
     * Site IDs deferred by a batched network migration.
     *
     * @return list<int>
     */
    protected function pendingSiteIds(): array
    {
        $stored = null;
        if (\function_exists('get_site_option')) {
            $stored = \get_site_option(self::PENDING_OPTION, []);
        } elseif (\function_exists('get_option')) {
            $stored = \get_option(self::PENDING_OPTION, []);
        }
        if (!\is_array($stored)) {
            return [];
        }

        return \array_values(\array_map(static fn ($siteId): int => (int) $siteId, $stored));
    }

    /**
     * Persist the deferred site-ID cursor.
     *
     * The cursor lives in the network option on multisite; the single-site
     * fallback uses the options table with autoload disabled so the cursor
     * never rides on every request.
     *
     * @param list<int> $siteIds Remaining site IDs (empty clears the cursor).
     */
    protected function storePendingSiteIds(array $siteIds): void
    {
        $siteIds = \array_values($siteIds);
        if (\function_exists('update_site_option')) {
            \update_site_option(self::PENDING_OPTION, $siteIds);

            return;
        }
        if (\function_exists('update_option')) {
            \update_option(self::PENDING_OPTION, $siteIds, false);
        }
    }

    /**
     * Whether this WordPress install runs multisite.
     */
    protected function isMultisite(): bool
    {
        return \function_exists('is_multisite') && \is_multisite();
    }

    /**
     * All site IDs for a network-wide loop.
     *
     * @return list<int>
     */
    protected function siteIds(): array
    {
        if (!\function_exists('get_sites')) {
            return [];
        }
        $ids = [];
        foreach (\get_sites(['fields' => 'ids', 'number' => 0]) as $siteId) {
            $ids[] = (int) $siteId;
        }

        return $ids;
    }

    /**
     * Switch the version-store context to the given site.
     */
    protected function switchToBlog(int $siteId): void
    {
        if (\function_exists('switch_to_blog')) {
            \switch_to_blog($siteId);
        }
    }

    /**
     * Restore the previous site context.
     */
    protected function restoreBlog(): void
    {
        if (\function_exists('restore_current_blog')) {
            \restore_current_blog();
        }
    }

    private function writeVersion(string $version): void
    {
        ($this->writer)($version);
    }
}
