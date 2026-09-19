<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database\Support;

/**
 * Minimal in-memory stand-in for the WordPress wpdb class.
 *
 * Implements only the surface consumed by BaseRepository so repository
 * behaviour is testable without WordPress. Writes land in the in-memory
 * `$tables` store (keyed by fully prefixed table name); prepared SELECTs
 * served through get_row()/get_results() are evaluated against that store
 * by a small parser that understands the canonical shapes repositories
 * emit: `FROM {table}`, equality `col = %s|%d`, `col LIKE %s` (substring,
 * `%`-wildcards stripped), OR-grouped LIKEs, `ORDER BY sort_order`,
 * `SELECT MAX(col)` aggregates, and `LIMIT %d OFFSET %d`. Placeholder args bind positionally via the
 * structured `$preparedArgs` log (never by re-splitting the rendered
 * string), so search input containing `|` stays intact.
 */
final class FakeWpdb
{
    /**
     * Table prefix.
     *
     * @var string
     */
    public $prefix = 'wp_';

    /**
     * Queries passed through prepare().
     *
     * @var list<string>
     */
    public array $queries = [];

    /**
     * Structured placeholder args per prepare() call, aligned with $queries.
     *
     * @var list<list<mixed>>
     */
    public array $preparedArgs = [];

    /**
     * In-memory rows keyed by fully prefixed table name.
     *
     * @var array<string, list<array<string, mixed>>>
     */
    public array $tables = [];

    /**
     * Last auto-increment id assigned by insert().
     */
    public int $insert_id = 0;

    /**
     * Auto-increment sequence.
     */
    private int $sequence = 0;

    /**
     * @param mixed ...$args
     */
    public function prepare(string $query, ...$args): string
    {
        $this->queries[] = $query;
        $this->preparedArgs[] = array_values($args);

        $rendered = array_map(static fn ($value): string => (string) $value, $args);

        return $query . '|' . implode(',', $rendered);
    }

    public function get_charset_collate(): string
    {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }

    /**
     * Insert a row into the in-memory store, assigning an auto-increment id.
     *
     * @param array<string, mixed> $data Column values (without id).
     * @return int|false 1 on success, mirroring wpdb::insert() rows-affected.
     */
    public function insert(string $table, array $data): int|false
    {
        $this->sequence++;
        $this->tables[$table][] = array_merge(['id' => $this->sequence], $data);
        $this->insert_id = $this->sequence;

        return 1;
    }

    /**
     * Update rows matching every $where equality in the in-memory store.
     *
     * @param array<string, mixed> $data  New column values.
     * @param array<string, mixed> $where Equality matchers.
     * @return int|false Number of rows updated.
     */
    public function update(string $table, array $data, array $where): int|false
    {
        if (!isset($this->tables[$table])) {
            return 0;
        }
        $affected = 0;
        foreach ($this->tables[$table] as &$row) {
            if (!self::matches($row, $where)) {
                continue;
            }
            $row = array_merge($row, $data);
            $affected++;
        }
        unset($row);

        return $affected;
    }

    /**
     * Delete rows matching every $where equality from the in-memory store.
     *
     * @param array<string, mixed> $where Equality matchers.
     * @return int|false Number of rows deleted.
     */
    public function delete(string $table, array $where): int|false
    {
        $before = \count($this->tables[$table] ?? []);
        $this->tables[$table] = array_values(
            array_filter(
                $this->tables[$table] ?? [],
                static fn (array $row): bool => !self::matches($row, $where)
            )
        );

        return $before - \count($this->tables[$table]);
    }

    /**
     * Evaluate a prepared SELECT against the in-memory store.
     *
     * @param mixed $output Ignored; rows always return as arrays (ARRAY_A shape).
     * @return list<array<string, mixed>>
     */
    public function get_results(string $query, mixed $output = null): array
    {
        return $this->select($query, null);
    }

    /**
     * Evaluate a prepared SELECT against the in-memory store, first row only.
     *
     * @param mixed $output Ignored; rows always return as arrays (ARRAY_A shape).
     * @return array<string, mixed>|null First matching row, or null.
     */
    public function get_row(string $query, mixed $output = null): ?array
    {
        $rows = $this->select($query, 1);

        return $rows[0] ?? null;
    }

    /**
     * Whether a stored row satisfies every equality matcher.
     *
     * @param array<string, mixed> $row   Stored row.
     * @param array<string, mixed> $where Equality matchers.
     */
    private static function matches(array $row, array $where): bool
    {
        foreach ($where as $column => $expected) {
            if (($row[$column] ?? null) != $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filter, sort, and slice the in-memory store for a prepared SELECT.
     *
     * @param int|null $limitOverride Hard row cap (get_row passes 1).
     * @return list<array<string, mixed>>
     */
    private function select(string $query, ?int $limitOverride): array
    {
        if (1 !== preg_match('/FROM\s+`?([a-zA-Z0-9_]+)`?/i', $query, $tableMatch)) {
            return [];
        }
        $rows = $this->tables[$tableMatch[1]] ?? [];
        $args = $this->argsFor($query);

        $rows = $this->applyWhere($query, $rows, $args);
        $aggregate = $this->applyMaxAggregate($query, $rows);
        if (null !== $aggregate) {
            $rows = [$aggregate];
        } else {
            $rows = $this->applyOrder($query, $rows);
        }

        [$limit, $offset] = $this->limitAndOffset($query, $args);
        if (null !== $limitOverride) {
            $limit = null === $limit ? $limitOverride : min($limit, $limitOverride);
        }

        return \array_slice(array_values($rows), $offset, $limit);
    }

    /**
     * Structured args for a prepared query (last matching prepare() call).
     *
     * @return list<mixed>
     */
    private function argsFor(string $query): array
    {
        for ($i = \count($this->queries) - 1; $i >= 0; $i--) {
            $rendered = $this->queries[$i] . '|' . implode(
                ',',
                array_map(static fn ($value): string => (string) $value, $this->preparedArgs[$i])
            );
            if ($rendered === $query) {
                return $this->preparedArgs[$i];
            }
        }

        return [];
    }

    /**
     * Apply the WHERE clause: equality parts ANDed, LIKE parts ORed within
     * a parenthesised group, args consumed in placeholder order.
     *
     * @param list<array<string, mixed>> $rows
     * @param list<mixed>                $args
     * @return list<array<string, mixed>>
     */
    private function applyWhere(string $query, array $rows, array $args): array
    {
        $where = '';
        if (1 === preg_match('/\bWHERE\b(.*?)(?:\bORDER BY\b|\bLIMIT\b|$)/is', $query, $whereMatch)) {
            $where = trim($whereMatch[1]);
        }
        if ('' === $where) {
            return $rows;
        }

        $conditions = preg_split('/\bAND\b/i', $where);
        if (false === $conditions) {
            return $rows;
        }

        $cursor = 0;
        $filters = [];
        foreach ($conditions as $condition) {
            $likeColumns = [];
            if (0 !== preg_match_all('/`?(\w+)`?\s+LIKE\s+%[ds]/i', $condition, $likeMatch)) {
                $likeColumns = $likeMatch[1];
            }
            if ([] !== $likeColumns) {
                $needles = [];
                foreach ($likeColumns as $column) {
                    $needles[$column] = self::needle((string) ($args[$cursor++] ?? ''));
                }
                $filters[] = static function (array $row) use ($needles): bool {
                    foreach ($needles as $column => $needle) {
                        $haystack = strtolower((string) ($row[$column] ?? ''));
                        if ('' !== $needle && str_contains($haystack, $needle)) {
                            return true;
                        }
                    }

                    return false;
                };
                continue;
            }
            if (1 === preg_match('/`?(\w+)`?\s*=\s*%[ds]/i', $condition, $equalityMatch)) {
                $column = $equalityMatch[1];
                $expected = $args[$cursor++] ?? null;
                $filters[] = static fn (array $row): bool => ($row[$column] ?? null) == $expected;
            }
        }

        return array_values(
            array_filter(
                $rows,
                static function (array $row) use ($filters): bool {
                    foreach ($filters as $filter) {
                        if (!$filter($row)) {
                            return false;
                        }
                    }

                    return true;
                }
            )
        );
    }

    /**
     * Evaluate SELECT MAX(col) AS alias against the filtered store.
     *
     * Repositories emit this shape for server-assigned appends
     * (maxSortOrder*()). Returns null when the query is not an aggregate so
     * normal row selection proceeds.
     *
     * @param list<array<string, mixed>> $rows
     * @return array<string, mixed>|null Single aggregate row, or null.
     */
    private function applyMaxAggregate(string $query, array $rows): ?array
    {
        if (1 !== preg_match('/SELECT\s+MAX\s*\(\s*`?(\w+)`?\s*\)(?:\s+AS\s+`?(\w+)`?)?/i', $query, $match)) {
            return null;
        }
        $column = $match[1];
        $alias = $match[2] ?? 'max_order';

        $max = null;
        foreach ($rows as $row) {
            $value = $row[$column] ?? null;
            if (!\is_numeric($value)) {
                continue;
            }
            $int = (int) $value;
            if (null === $max || $int > $max) {
                $max = $int;
            }
        }

        return [$alias => $max];
    }

    /**
     * Apply ORDER BY: sort_order ascending (then id) when requested.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function applyOrder(string $query, array $rows): array
    {
        if (1 !== preg_match('/\bORDER BY\b/i', $query)) {
            return $rows;
        }

        usort(
            $rows,
            static function (array $a, array $b): int {
                $order = ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));
                if (0 !== $order) {
                    return $order;
                }

                return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
            }
        );

        return $rows;
    }

    /**
     * Read LIMIT/OFFSET placeholder args (last two positional args when the
     * clause is present).
     *
     * @param list<mixed> $args
     * @return array{0: int|null, 1: int}
     */
    private function limitAndOffset(string $query, array $args): array
    {
        if (1 !== preg_match('/\bLIMIT\b/i', $query)) {
            return [null, 0];
        }

        $count = \count($args);
        if (1 === preg_match('/\bOFFSET\b/i', $query)) {
            return [(int) ($args[$count - 2] ?? 0), (int) ($args[$count - 1] ?? 0)];
        }

        return [(int) ($args[$count - 1] ?? 0), 0];
    }

    /**
     * LIKE needle: strip % wildcards, compare case-insensitively.
     */
    private static function needle(string $pattern): string
    {
        return strtolower(trim($pattern, '%'));
    }
}
