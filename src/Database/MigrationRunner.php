<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database;

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
 * This change ships the runner contract plus the multisite loop only.
 * Real domain tables land in follow-up issues, which append their
 * version-guarded migrations to defaults().
 */
class MigrationRunner
{
    /**
     * Canonical database-version option key.
     */
    public const OPTION = 'smooth_db_version';

    /**
     * Database schema version this plugin code understands.
     */
    public const TARGET_VERSION = '0.1.0';

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
     * additive migrations here. The runner contract and multisite loop ship
     * now; no domain tables yet.
     *
     * @return array<string, callable(): void>
     */
    public static function defaults(): array
    {
        return [];
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
     * Each site keeps its own smooth_db_version value.
     */
    public function migrateAll(bool $networkWide = false): void
    {
        if ($networkWide && $this->isMultisite()) {
            foreach ($this->siteIds() as $siteId) {
                $this->switchToBlog($siteId);
                try {
                    $this->migrate();
                } finally {
                    $this->restoreBlog();
                }
            }

            return;
        }
        $this->migrate();
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
