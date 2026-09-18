<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Database\BaseRepository;

/**
 * Transactions ledger table repository.
 *
 * The ledger is append-only: refunds are new rows, never updates or deletes.
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class TransactionRepository extends BaseRepository
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
            . "idempotency_key varchar(64) NOT NULL DEFAULT '',\n"
            . "created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY order_id (order_id),' . "\n"
            . 'UNIQUE KEY idempotency_key (idempotency_key)';
    }
}
