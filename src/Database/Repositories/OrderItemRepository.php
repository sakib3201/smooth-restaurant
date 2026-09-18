<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\OrderItemRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Order items table repository.
 *
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_order_items';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'order_id', 'qty', 'unit_cents'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "order_id bigint(20) unsigned NOT NULL,\n"
            . "name varchar(191) NOT NULL DEFAULT '',\n"
            . "qty int(11) NOT NULL DEFAULT 1,\n"
            . "unit_cents bigint(20) NOT NULL DEFAULT 0,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY order_id (order_id)';
    }
}
