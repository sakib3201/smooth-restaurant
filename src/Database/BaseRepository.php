<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database;

use SmoothRestaurant\Exceptions\RepositoryException;

/**
 * Shared base for custom-table repositories.
 *
 * Owns the connection plumbing every domain repository needs: site table
 * prefix, charset/collation suffix, statement preparation, and typed-row
 * hydration. Query logic lives in the per-table subclasses; raw `$wpdb`
 * access outside `src/Database/` is forbidden and guarded by the
 * no-postmeta architecture test.
 *
 * Schema rule: dbDelta-first. Subclasses expose their `CREATE TABLE`
 * statement via `schema()`; raw SQL is reserved for indexes or keys dbDelta
 * cannot express. Table creation never runs in the request path — it is
 * invoked from migrations on activation or from background jobs only.
 */
abstract class BaseRepository
{
    /**
     * Raw database connection (`wpdb` in production, test double in unit tests).
     */
    protected object $wpdb;

    /**
     * Site-specific table prefix captured at construction.
     */
    protected string $prefix;

    /**
     * @param object|null $wpdb Raw database connection (`wpdb` in production,
     *                          test double in unit tests). Null falls back to
     *                          the global connection when available, else a
     *                          minimal default so the container can auto-wire
     *                          repositories without touching `$wpdb` outside
     *                          `src/Database/`.
     */
    public function __construct(?object $wpdb = null)
    {
        $wpdb ??= $GLOBALS['wpdb'] ?? null;
        if (! is_object($wpdb)) {
            $wpdb         = new \stdClass();
            $wpdb->prefix = 'wp_';
        }
        $this->wpdb   = $wpdb;
        $vars         = \get_object_vars($wpdb);
        $prefix       = $vars['prefix'] ?? 'wp_';
        $this->prefix = \is_string($prefix) && '' !== $prefix ? $prefix : 'wp_';
    }

    /**
     * Fully prefixed table name for this repository.
     */
    public function getTable(): string
    {
        return $this->prefix . $this->tableSuffix();
    }

    /**
     * Unprefixed table name, e.g. `smooth_orders`.
     */
    abstract protected function tableSuffix(): string;

    /**
     * Columns holding integer values, cast on hydration.
     *
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id'];
    }

    /**
     * Column and key definitions for dbDelta (everything between the parens).
     */
    abstract protected function columnDefinitions(): string;

    /**
     * dbDelta-first `CREATE TABLE` statement for this repository's table.
     */
    public function schema(): string
    {
        return 'CREATE TABLE ' . $this->getTable() . " (\n"
            . $this->columnDefinitions() . "\n) " . $this->charsetCollate() . ';';
    }

    /**
     * Create (or update) this repository's table via dbDelta.
     *
     * @throws RepositoryException When the WordPress upgrade API is unavailable.
     */
    public function createTable(): void
    {
        if (!\function_exists('dbDelta')) {
            $upgrade = \defined('ABSPATH') ? (string) \constant('ABSPATH') . 'wp-admin/includes/upgrade.php' : '';
            if ('' !== $upgrade && \is_readable($upgrade)) {
                require_once $upgrade;
            }
        }

        if (!\function_exists('dbDelta')) {
            throw new RepositoryException('WordPress upgrade API (dbDelta) is not available.');
        }

        \dbDelta($this->schema());
    }

    /**
     * Map raw database rows to typed rows.
     *
     * Integer columns (see `intColumns()`) arriving as numeric strings are
     * cast to int; every other value passes through untouched.
     *
     * @param list<array<string, mixed>> $rows Raw rows.
     * @return list<array<string, mixed>> Typed rows.
     */
    public function mapRows(array $rows): array
    {
        $ints   = \array_fill_keys($this->intColumns(), true);
        $mapped = [];
        foreach ($rows as $row) {
            foreach ($row as $column => $value) {
                if (isset($ints[$column]) && (\is_int($value) || (\is_string($value) && \is_numeric($value)))) {
                    $row[$column] = (int) $value;
                }
            }

            $mapped[] = $row;
        }

        return $mapped;
    }

    /**
     * Connection charset/collation suffix for `CREATE TABLE`.
     */
    protected function charsetCollate(): string
    {
        $db = $this->wpdb;
        if (\method_exists($db, 'get_charset_collate')) {
            $value = $db->get_charset_collate();
            if (\is_string($value) && '' !== $value) {
                return $value;
            }
        }

        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }

    /**
     * Prepare a query through the connection.
     *
     * @throws RepositoryException When the connection cannot prepare the query.
     */
    protected function prepare(string $query, mixed ...$args): string
    {
        $db = $this->wpdb;
        if (!\method_exists($db, 'prepare')) {
            throw new RepositoryException('Database connection does not support prepare().');
        }

        $prepared = $db->prepare($query, ...$args);
        if (!\is_string($prepared)) {
            throw new RepositoryException('Database connection failed to prepare the query.');
        }

        return $prepared;
    }

    /**
     * Insert a row through the connection, returning the new id.
     *
     * @param array<string, mixed> $data Column values (without id).
     * @throws RepositoryException When the connection cannot insert.
     */
    protected function insertRow(string $table, array $data): int
    {
        $db = $this->wpdb;
        if (!\method_exists($db, 'insert')) {
            throw new RepositoryException('Database connection does not support insert().');
        }

        if (false === $db->insert($table, $data)) {
            throw new RepositoryException('Database insert failed.');
        }
        if (!\property_exists($db, 'insert_id')) {
            throw new RepositoryException('Database connection does not expose insert_id.');
        }

        return (int) $db->insert_id;
    }

    /**
     * Update rows matching every $where equality through the connection.
     *
     * @param array<string, mixed> $data  New column values.
     * @param array<string, mixed> $where Equality matchers.
     * @return int Number of rows updated.
     * @throws RepositoryException When the connection cannot update.
     */
    protected function updateRows(string $table, array $data, array $where): int
    {
        $db = $this->wpdb;
        if (!\method_exists($db, 'update')) {
            throw new RepositoryException('Database connection does not support update().');
        }

        $affected = $db->update($table, $data, $where);
        if (false === $affected) {
            throw new RepositoryException('Database update failed.');
        }

        return (int) $affected;
    }

    /**
     * Delete rows matching every $where equality through the connection.
     *
     * @param array<string, mixed> $where Equality matchers.
     * @return int Number of rows deleted.
     * @throws RepositoryException When the connection cannot delete.
     */
    protected function deleteRows(string $table, array $where): int
    {
        $db = $this->wpdb;
        if (!\method_exists($db, 'delete')) {
            throw new RepositoryException('Database connection does not support delete().');
        }

        $deleted = $db->delete($table, $where);
        if (false === $deleted) {
            throw new RepositoryException('Database delete failed.');
        }

        return (int) $deleted;
    }

    /**
     * Fetch one row as an associative array through the connection.
     *
     * Callers pass a query already run through prepare().
     *
     * @return array<string, mixed>|null The row, or null when no row matches.
     * @throws RepositoryException When the connection cannot fetch rows.
     */
    protected function fetchRow(string $preparedQuery): ?array
    {
        $db = $this->wpdb;
        if (!\method_exists($db, 'get_row')) {
            throw new RepositoryException('Database connection does not support get_row().');
        }

        $row = $db->get_row($preparedQuery, 'ARRAY_A');
        if (null === $row) {
            return null;
        }
        if (!\is_array($row)) {
            throw new RepositoryException('Database connection returned an unexpected row shape.');
        }

        /** @var array<string, mixed> $row */
        return $row;
    }

    /**
     * Fetch rows as associative arrays through the connection.
     *
     * Callers pass a query already run through prepare().
     *
     * @return list<array<string, mixed>>
     * @throws RepositoryException When the connection cannot fetch rows.
     */
    protected function fetchAll(string $preparedQuery): array
    {
        $db = $this->wpdb;
        if (!\method_exists($db, 'get_results')) {
            throw new RepositoryException('Database connection does not support get_results().');
        }

        $rows = $db->get_results($preparedQuery, 'ARRAY_A');
        if (!\is_array($rows)) {
            throw new RepositoryException('Database connection returned an unexpected result shape.');
        }

        /** @var list<array<string, mixed>> $rows */
        return array_values($rows);
    }
}
