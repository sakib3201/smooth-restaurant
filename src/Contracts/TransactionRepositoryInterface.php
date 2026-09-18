<?php

/**
 * Transaction repository contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface TransactionRepositoryInterface
 *
 * Public surface of the append-only transactions ledger repository.
 */
interface TransactionRepositoryInterface
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
     * Generate an idempotency key for a ledger insert.
     *
     * @return string
     */
    public static function generateIdempotencyKey(): string;
}
