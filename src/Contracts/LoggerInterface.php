<?php

/**
 * Logger contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface LoggerInterface
 *
 * Minimal PSR-3-shaped logging contract so runtime diagnostics
 * (e.g. skipped providers) stay testable via test doubles without
 * WordPress loaded.
 */
interface LoggerInterface
{
    /**
     * Log a debug message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Log an informational message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function info(string $message, array $context = []): void;

    /**
     * Log a warning.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Log an error.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function error(string $message, array $context = []): void;
}
