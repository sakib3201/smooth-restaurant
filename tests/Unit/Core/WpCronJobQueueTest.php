<?php

/**
 * Unit tests for the WP-Cron job queue.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Contracts\LoggerInterface;
use SmoothRestaurant\Core\WpCronJobQueue;

/**
 * Class WpCronJobQueueTest
 *
 * WordPress is not loaded in unit context, so these tests exercise the
 * guarded no-op path: nothing throws, and each call records a debug note.
 */
class WpCronJobQueueTest extends TestCase
{
    /**
     * Test that all operations are safe no-ops without WordPress.
     *
     * @return void
     */
    public function test_operations_noop_without_wordpress_and_log_debug(): void
    {
        $logger = new class () implements LoggerInterface {
            /**
             * @var list<array{message: string, context: array<string, mixed>}>
             */
            public array $notes = array();

            public function debug(string $message, array $context = array()): void
            {
                $this->notes[] = array( 'message' => $message, 'context' => $context );
            }

            public function info(string $message, array $context = array()): void
            {
            }

            public function warning(string $message, array $context = array()): void
            {
            }

            public function error(string $message, array $context = array()): void
            {
            }
        };

        $queue = new WpCronJobQueue($logger);
        $queue->schedule('smooth_test_hook', array( 'id' => 1 ), 60);
        $queue->scheduleRecurring('smooth_test_hook', array(), 3600);
        $queue->cancel('smooth_test_hook', array( 'id' => 1 ));

        $this->assertCount(3, $logger->notes);
        $this->assertSame('smooth.jobqueue.noop.schedule', $logger->notes[0]['message']);
        $this->assertSame('smooth.jobqueue.noop.schedule_recurring', $logger->notes[1]['message']);
        $this->assertSame('smooth.jobqueue.noop.cancel', $logger->notes[2]['message']);
        $this->assertSame(array( 'hook' => 'smooth_test_hook' ), $logger->notes[0]['context']);
    }

    /**
     * Test that the queue constructs without a logger.
     *
     * @return void
     */
    public function test_constructs_without_logger(): void
    {
        $queue = new WpCronJobQueue();
        $queue->schedule('smooth_test_hook');

        $this->assertInstanceOf(WpCronJobQueue::class, $queue);
    }
}
