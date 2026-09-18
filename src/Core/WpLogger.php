<?php

/**
 * WordPress error_log logger.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

use SmoothRestaurant\Contracts\LoggerInterface;

/**
 * Class WpLogger
 *
 * Default `LoggerInterface` implementation: writes to `error_log` with the
 * `[Smooth Restaurant]` prefix. Non-empty context is appended as JSON.
 */
final class WpLogger implements LoggerInterface
{
    /**
     * Log a debug message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->write('debug', $message, $context);
    }

    /**
     * Log an informational message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /**
     * Log a warning.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /**
     * Log an error.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /**
     * Write one line to the error log.
     *
     * @param string               $level   Log level.
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    private function write(string $level, string $message, array $context): void
    {
        $line = sprintf('[Smooth Restaurant] %s: %s', $level, $message);
        if ([] !== $context) {
            $encoded = json_encode($context);
            $line   .= ' ' . (is_string($encoded) ? $encoded : '[]');
        }
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.ErrorLog -- intentional runtime log; test doubles replace this logger in unit tests.
        error_log($line);
    }
}
