<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Database\BaseRepository;

/**
 * Reservations table repository.
 *
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class ReservationRepository extends BaseRepository
{
    protected function tableSuffix(): string
    {
        return 'smooth_reservations';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'party_size'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "status varchar(32) NOT NULL DEFAULT 'pending',\n"
            . "party_size int(11) NOT NULL DEFAULT 2,\n"
            . "reserved_for datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\n"
            . "created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY reserved_for (reserved_for)';
    }
}
