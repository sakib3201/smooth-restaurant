<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\CartRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Guest + login carts repository.
 *
 * Carts pair a server-side draft row with a signed cookie (no PHP sessions).
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_carts';
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "session_key varchar(64) NOT NULL,\n"
            . "payload longtext NOT NULL,\n"
            . "expires_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'UNIQUE KEY session_key (session_key)';
    }

    /**
     * Generate a session key for a cart insert.
     *
     * Pure PHP with no WordPress dependency so cart writes stay
     * unit-testable. Returns 32 lowercase hex chars from random_bytes().
     * The column carries no default, so writers MUST set a generated key —
     * keyless inserts would otherwise collide on the empty string.
     */
    public static function generateSessionKey(): string
    {
        return \bin2hex(\random_bytes(16));
    }
}
