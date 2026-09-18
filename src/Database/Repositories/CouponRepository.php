<?php

declare(strict_types=1);

namespace SmoothRestaurant\Database\Repositories;

use SmoothRestaurant\Contracts\CouponRepositoryInterface;
use SmoothRestaurant\Database\BaseRepository;

/**
 * Coupons table repository.
 *
 * Thin shell over BaseRepository; domain query methods land in follow-up
 * issues. Schema is dbDelta-managed; raw SQL only for keys dbDelta cannot
 * express.
 */
class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    protected function tableSuffix(): string
    {
        return 'smooth_coupons';
    }

    /**
     * @return list<string>
     */
    protected function intColumns(): array
    {
        return ['id', 'amount_cents'];
    }

    protected function columnDefinitions(): string
    {
        return "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "code varchar(64) NOT NULL DEFAULT '',\n"
            . "status varchar(32) NOT NULL DEFAULT 'active',\n"
            . "amount_cents bigint(20) NOT NULL DEFAULT 0,\n"
            . 'PRIMARY KEY  (id),' . "\n"
            . 'UNIQUE KEY code (code)';
    }
}
