<?php

/**
 * Recording notifier fake.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Testing;

use SmoothRestaurant\Contracts\NotifierInterface;

/**
 * Class RecordingNotifier
 *
 * `NotifierInterface` fake that stores sent messages for assertions, with
 * zero WordPress dependencies.
 */
final class RecordingNotifier implements NotifierInterface
{
    /**
     * Stored messages, in send order.
     *
     * @var list<array<string, mixed>>
     */
    private array $sent = array();

    /**
     * Constructor.
     *
     * @param string $channelId Channel identifier.
     */
    public function __construct(private string $channelId = 'test')
    {
    }

    /**
     * Channel identifier.
     *
     * @return string
     */
    public function channel(): string
    {
        return $this->channelId;
    }

    /**
     * Store the message for assertions.
     *
     * @param array<string, mixed> $message Message payload.
     * @return void
     */
    public function notify(array $message): void
    {
        $this->sent[] = $message;
    }

    /**
     * Stored messages, in send order.
     *
     * @return list<array<string, mixed>>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    /**
     * Clear stored messages.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->sent = array();
    }
}
