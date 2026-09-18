<?php

/**
 * Notifications domain service shell.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Notifications;

/**
 * Class NotificationService
 *
 * Shell: pure notification logic lands with the notifications follow-up
 * issue. Channel implementations satisfy Contracts\NotifierInterface.
 * Stays dependency-free so providers can bind it in register() with
 * no I/O, hooks, or translation calls.
 */
final class NotificationService
{
}
