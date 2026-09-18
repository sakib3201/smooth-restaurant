<?php

/**
 * Modifier repository contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface ModifierRepositoryInterface
 *
 * Public surface of the modifiers table repository.
 */
interface ModifierRepositoryInterface
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
     * Insert a modifier row, returning the new id.
     *
     * @param array<string, mixed> $data Column values (without id).
     * @return int New row id.
     */
    public function insert(array $data): int;

    /**
     * Find a modifier row by id.
     *
     * @param int $id Row id.
     * @return array<string, mixed>|null The typed row, or null when missing.
     */
    public function findById(int $id): ?array;

    /**
     * List modifier rows for one menu item in display order.
     *
     * @param int $itemId Menu item row id.
     * @return list<array<string, mixed>> Typed rows.
     */
    public function listByItem(int $itemId): array;

    /**
     * Update a modifier row by id.
     *
     * @param int                  $id   Row id.
     * @param array<string, mixed> $data New column values.
     * @return bool Whether a row was updated.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a modifier row by id.
     *
     * @param int $id Row id.
     * @return bool Whether a row was deleted.
     */
    public function delete(int $id): bool;
}
