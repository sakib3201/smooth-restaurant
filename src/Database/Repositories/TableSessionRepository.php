<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Database\BaseRepository;

/**
 * QR table-sessions repository.
 *
 * Sessions are real rows with TTL (never transients), giving expiry plus a
 * single-active-session audit trail. Thin shell over BaseRepository; domain
 * query methods land in follow-up issues. Schema is dbDelta-managed; raw SQL
 * only for keys dbDelta cannot express.
 */
class TableSessionRepository extends BaseRepository
{
    protected function tableSuffix(): string
    {
        return 'smooth_table_sessions';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'table_id'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "table_id bigint(20) unsigned NOT NULL,\n"
            . "token varchar(64) NOT NULL DEFAULT '',\n"
            . "status varchar(32) NOT NULL DEFAULT 'active',\n"
            . "expires_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY table_id (table_id),' . "\n"
            . 'UNIQUE KEY token (token)';
    }
}
