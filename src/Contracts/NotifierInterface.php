<?php

/**
 * Notifier contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface NotifierInterface
 *
 * Sends order/reservation notifications over one channel (e.g. email,
 * SMS via a Pro addon). Messages are plain arrays so notifiers stay
 * testable without WordPress loaded.
 */
interface NotifierInterface
{
    /**
     * Channel identifier (e.g. 'email', 'sms').
     *
     * @return string
     */
    public function channel(): string;

    /**
     * Send a notification.
     *
     * @param array<string, mixed> $message Message payload.
     * @return void
     */
    public function notify(array $message): void;
}
