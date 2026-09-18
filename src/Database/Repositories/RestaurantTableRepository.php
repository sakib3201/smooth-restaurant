<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Database\BaseRepository;

/**
 * Restaurant tables (floor plan) repository.
 *
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class RestaurantTableRepository extends BaseRepository
{
    protected function tableSuffix(): string
    {
        return 'smooth_tables';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'seats'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "label varchar(64) NOT NULL DEFAULT '',\n"
            . "seats int(11) NOT NULL DEFAULT 2,\n"
            . "status varchar(32) NOT NULL DEFAULT 'active',\n"
            . 'PRIMARY KEY  (id)';
    }
}
