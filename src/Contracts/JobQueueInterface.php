<?php

/**
 * Background-job queue contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface JobQueueInterface
 *
 * Abstracts background work so domains never call WP-Cron directly.
 *
 * Decision: abstract now on top of WP-Cron (`WpCronJobQueue`), leaving an
 * Action Scheduler swap later without touching domain call sites. A future
 * `ActionSchedulerJobQueue` can implement this same interface and replace
 * the binding in `CoreProvider::register()`; hook names, args shapes, and
 * delay/interval semantics stay stable across the swap.
 */
interface JobQueueInterface
{
    /**
     * Schedule a one-off job.
     *
     * @param string               $hook         Job hook name.
     * @param array<string, mixed> $args         Job arguments.
     * @param int                  $delaySeconds Delay before the run, in seconds.
     * @return void
     */
    public function schedule(string $hook, array $args = array(), int $delaySeconds = 0): void;

    /**
     * Schedule a recurring job.
     *
     * @param string               $hook            Job hook name.
     * @param array<string, mixed> $args            Job arguments.
     * @param int                  $intervalSeconds Repeat interval, in seconds.
     * @return void
     */
    public function scheduleRecurring(string $hook, array $args, int $intervalSeconds): void;

    /**
     * Cancel scheduled jobs.
     *
     * @param string               $hook Job hook name.
     * @param array<string, mixed> $args Job arguments.
     * @return void
     */
    public function cancel(string $hook, array $args = array()): void;
}
