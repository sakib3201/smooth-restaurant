<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Database\BaseRepository;

/**
 * Guest + login carts repository.
 *
 * Carts pair a server-side draft row with a signed cookie (no PHP sessions).
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class CartRepository extends BaseRepository
{
    protected function tableSuffix(): string
    {
        return 'smooth_carts';
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "session_key varchar(64) NOT NULL DEFAULT '',\n"
            . "payload longtext NOT NULL,\n"
            . "expires_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'UNIQUE KEY session_key (session_key)';
    }
}
