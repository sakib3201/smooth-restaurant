<?php

/**
 * Payments domain service shell.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Domains\Payments;

/**
 * Class PaymentService
 *
 * Shell: pure payment domain logic lands with the payments follow-up
 * issue. Gateway implementations satisfy Contracts\GatewayInterface.
 * Stays dependency-free so providers can bind it in register() with
 * no I/O, hooks, or translation calls.
 */
final class PaymentService
{
}
