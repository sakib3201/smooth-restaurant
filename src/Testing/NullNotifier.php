<?php

/**
 * Null notifier fake.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Testing;

use SmoothRestaurant\Contracts\NotifierInterface;

/**
 * Class NullNotifier
 *
 * No-op `NotifierInterface` fake with zero WordPress dependencies.
 * Useful as a default binding where notifications must be silenced.
 */
final class NullNotifier implements NotifierInterface
{
    /**
     * Channel identifier.
     *
     * @return string
     */
    public function channel(): string
    {
        return 'null';
    }

    /**
     * Discard the message.
     *
     * @param array<string, mixed> $message Message payload.
     * @return void
     */
    public function notify(array $message): void
    {
        unset($message);
    }
}
