<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\TransactionRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Transactions ledger table repository.
 *
 * The ledger is append-only: refunds are new rows, never updates or deletes.
 * Every insert MUST carry an idempotency key (see generateIdempotencyKey()):
 * the column is NOT NULL + UNIQUE with no default, so keyless inserts would
 * collide on the empty string. Thin shell over BaseRepository; domain query
 * methods land in follow-up issues. Schema is dbDelta-managed; raw SQL only
 * for keys dbDelta cannot express.
 */
class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_transactions';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'order_id', 'amount_cents'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "order_id bigint(20) unsigned NOT NULL,\n"
            . "gateway varchar(32) NOT NULL DEFAULT '',\n"
            . "mode varchar(16) NOT NULL DEFAULT 'live',\n"
            . "amount_cents bigint(20) NOT NULL DEFAULT 0,\n"
            . "status varchar(32) NOT NULL DEFAULT 'pending',\n"
            . "idempotency_key varchar(64) NOT NULL,\n"
            . "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY order_id (order_id),' . "\n"
            . 'UNIQUE KEY idempotency_key (idempotency_key)';
    }

    /**
     * Generate an idempotency key for a ledger insert.
     *
     * Pure PHP with no WordPress dependency so ledger writes stay
     * unit-testable. Returns 32 lowercase hex chars from random_bytes().
     */
    public static function generateIdempotencyKey(): string
    {
        return \bin2hex(\random_bytes(16));
    }
}
