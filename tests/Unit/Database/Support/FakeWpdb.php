<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database\Support;

/**
 * Minimal in-memory stand-in for the WordPress wpdb class.
 *
 * Implements only the surface consumed by BaseRepository so repository
 * behaviour is testable without WordPress.
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
     * @param mixed ...$args
     */
    public function prepare(string $query, ...$args): string
    {
        $this->queries[] = $query;

        $rendered = array_map(static fn ($value): string => (string) $value, $args);

        return $query . '|' . implode(',', $rendered);
    }

    public function get_charset_collate(): string
    {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }
}
