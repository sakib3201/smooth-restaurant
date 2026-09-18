<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\OrderRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Orders table repository.
 *
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_orders';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'total_cents'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "status varchar(32) NOT NULL DEFAULT 'pending',\n"
            . "currency char(3) NOT NULL DEFAULT 'USD',\n"
            . "total_cents bigint(20) NOT NULL DEFAULT 0,\n"
            . "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . "updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY status_created (status, created_at)';
    }
}
