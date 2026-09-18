<?php

/**
 * Menu repository contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface MenuRepositoryInterface
 *
 * Public surface of the menus table repository.
 */
interface MenuRepositoryInterface
{
    /**
     * Fully prefixed table name.
     *
     * @return string
     */
    public function getTable(): string;

    /**
     * dbDelta-first `CREATE TABLE` statement.
     *
     * @return string
     */
    public function schema(): string;

    /**
     * Map raw database rows to typed rows.
     *
     * @param list<array<string, mixed>> $rows Raw rows.
     * @return list<array<string, mixed>> Typed rows.
     */
    public function mapRows(array $rows): array;

    /**
     * Create (or update) the table via dbDelta.
     *
     * @return void
     */
    public function createTable(): void;

    /**
     * Insert a menu row, returning the new id.
     *
     * @param array<string, mixed> $data Column values (without id).
     * @return int New row id.
     */
    public function insert(array $data): int;

    /**
     * Find a menu row by id.
     *
     * @param int $id Row id.
     * @return array<string, mixed>|null The typed row, or null when missing.
     */
    public function findById(int $id): ?array;

    /**
     * Paginate menu rows, newest sort-order first.
     *
     * @param int    $page    1-based page number (values below 1 behave as 1).
     * @param int    $perPage Rows per page, clamped to 1–100.
     * @param string $search  Optional substring matched against name and description.
     * @param string $status  Row status filter.
     * @return list<array<string, mixed>> Typed rows.
     */
    public function paginate(int $page, int $perPage, string $search = '', string $status = 'publish'): array;

    /**
     * Update a menu row by id.
     *
     * @param int                  $id   Row id.
     * @param array<string, mixed> $data New column values.
     * @return bool Whether a row was updated.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a menu row by id.
     *
     * @param int $id Row id.
     * @return bool Whether a row was deleted.
     */
    public function delete(int $id): bool;

    /**
     * Generate a URL-safe slug for a menu name.
     *
     * Pure helper: lowercase, non-alphanumerics collapsed to hyphens.
     * Callers MUST handle uniqueness (append a suffix on slug collision) —
     * the column carries no default, so writers MUST set a generated slug.
     *
     * @param string $name Menu name.
     * @return string Slug (never empty; falls back to 'menu').
     */
    public static function generateSlug(string $name): string;
}
