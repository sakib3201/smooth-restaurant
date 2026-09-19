<?php

/**
 * Menu item repository contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface MenuItemRepositoryInterface
 *
 * Public surface of the menu items table repository.
 */
interface MenuItemRepositoryInterface
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
     * Insert a menu item row, returning the new id.
     *
     * @param array<string, mixed> $data Column values (without id).
     * @return int New row id.
     */
    public function insert(array $data): int;

    /**
     * Find a menu item row by id.
     *
     * @param int $id Row id.
     * @return array<string, mixed>|null The typed row, or null when missing.
     */
    public function findById(int $id): ?array;

    /**
     * List item rows for one menu in display order.
     *
     * @param int    $menuId Menu row id.
     * @param string $status Row status filter.
     * @return list<array<string, mixed>> Typed rows.
     */
    public function listByMenu(int $menuId, string $status = 'publish'): array;

    /**
     * Highest display order within one menu, or null when empty.
     *
     * Backs server-assigned appends: create without sort_order lands at
     * MAX(sort_order)+1.
     *
     * @param int $menuId Menu row id.
     * @return int|null Highest sort_order, or null when the menu has no items.
     */
    public function maxSortOrderForMenu(int $menuId): ?int;

    /**
     * Update a menu item row by id.
     *
     * @param int                  $id   Row id.
     * @param array<string, mixed> $data New column values.
     * @return bool Whether a row was updated.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a menu item row by id.
     *
     * @param int $id Row id.
     * @return bool Whether a row was deleted.
     */
    public function delete(int $id): bool;
}
