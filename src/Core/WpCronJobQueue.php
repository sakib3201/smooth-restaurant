<?php

/**
 * WP-Cron job queue.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

use SmoothRestaurant\Contracts\JobQueueInterface;
use SmoothRestaurant\Contracts\LoggerInterface;

/**
 * Class WpCronJobQueue
 *
 * `JobQueueInterface` implementation on top of WP-Cron. Every WordPress
 * API call is `function_exists`-guarded: without WordPress (unit tests)
 * each method is a no-op that records a `LoggerInterface::debug` note.
 * The logger is an optional constructor dependency defaulting to null so
 * the queue stays constructible without a container.
 */
final class WpCronJobQueue implements JobQueueInterface
{
    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger Optional logger for no-op notes.
     */
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }

    /**
     * Schedule a one-off job via `wp_schedule_single_event()`.
     *
     * @param string               $hook         Job hook name.
     * @param array<string, mixed> $args         Job arguments.
     * @param int                  $delaySeconds Delay before the run, in seconds.
     * @return void
     */
    public function schedule(string $hook, array $args = array(), int $delaySeconds = 0): void
    {
        if (! function_exists('wp_schedule_single_event')) {
            $this->logger?->debug('smooth.jobqueue.noop.schedule', array( 'hook' => $hook ));

            return;
        }

        $delay = max(0, $delaySeconds);
        // WP-Cron passes args positionally: normalize string keys away so the
        // scheduled payload matches the declared array<string, mixed> surface.
        wp_schedule_single_event(time() + $delay, $hook, array_values($args));
    }

    /**
     * Schedule a recurring job via `wp_schedule_event()`.
     *
     * Known WP-Cron recurrences (`hourly`, `twicedaily`, `daily`,
     * `weekly`) are reused on exact interval matches; any other interval
     * uses a `smooth_every_<seconds>` slug that a `cron_schedules` filter
     * (or the future Action Scheduler swap) must provide.
     *
     * @param string               $hook            Job hook name.
     * @param array<string, mixed> $args            Job arguments.
     * @param int                  $intervalSeconds Repeat interval, in seconds.
     * @return void
     */
    public function scheduleRecurring(string $hook, array $args, int $intervalSeconds): void
    {
        if (! function_exists('wp_schedule_event')) {
            $this->logger?->debug('smooth.jobqueue.noop.schedule_recurring', array( 'hook' => $hook ));

            return;
        }

        wp_schedule_event(time(), $this->recurrence($intervalSeconds), $hook, array_values($args));
    }

    /**
     * Cancel scheduled jobs via `wp_clear_scheduled_hook()`.
     *
     * @param string               $hook Job hook name.
     * @param array<string, mixed> $args Job arguments.
     * @return void
     */
    public function cancel(string $hook, array $args = array()): void
    {
        if (! function_exists('wp_clear_scheduled_hook')) {
            $this->logger?->debug('smooth.jobqueue.noop.cancel', array( 'hook' => $hook ));

            return;
        }

        wp_clear_scheduled_hook($hook, array_values($args));
    }

    /**
     * Map an interval in seconds to a WP-Cron recurrence slug.
     *
     * @param int $intervalSeconds Repeat interval, in seconds.
     * @return string Recurrence slug.
     */
    private function recurrence(int $intervalSeconds): string
    {
        if (3600 === $intervalSeconds) {
            return 'hourly';
        }

        if (43200 === $intervalSeconds) {
            return 'twicedaily';
        }

        if (86400 === $intervalSeconds) {
            return 'daily';
        }

        if (604800 === $intervalSeconds) {
            return 'weekly';
        }

        return 'smooth_every_' . max(1, $intervalSeconds);
    }
}
