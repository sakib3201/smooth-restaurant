<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Database\BaseRepository;

/**
 * Notification queue repository.
 *
 * Queued sends with retry/backoff bookkeeping (`status`, `attempts`,
 * `next_try`). Thin shell over BaseRepository; domain query methods land in
 * follow-up issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta
 * cannot express.
 */
class NotificationRepository extends BaseRepository
{
    protected function tableSuffix(): string
    {
        return 'smooth_notifications';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'attempts'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "channel varchar(32) NOT NULL DEFAULT 'email',\n"
            . "status varchar(32) NOT NULL DEFAULT 'queued',\n"
            . "attempts int(11) NOT NULL DEFAULT 0,\n"
            . "next_try datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\n"
            . "payload longtext NOT NULL,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'KEY status_next_try (status, next_try)';
    }
}
