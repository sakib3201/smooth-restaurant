<?php

/**
 * Unit tests for domain events.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Domains\Shared;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Domains\Shared\DomainEvents;

/**
 * Class DomainEventsTest
 */
class DomainEventsTest extends TestCase
{
    /**
     * Set up clean stub state for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        sr_test_reset_stubs();
    }

    /**
     * Tear down stub state after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        parent::tearDown();
    }

    /**
     * Test that event names use the smooth.* namespace.
     *
     * @return void
     */
    public function test_event_names_are_smooth_namespaced(): void
    {
        foreach (
            array(
            DomainEvents::ORDER_CREATED,
            DomainEvents::PAYMENT_CAPTURED,
            DomainEvents::PAYMENT_REFUNDED,
            DomainEvents::RESERVATION_CONFIRMED,
            DomainEvents::CART_UPDATED,
            ) as $event
        ) {
            $this->assertStringStartsWith('smooth.', $event);
        }
    }

    /**
     * Test that dispatch forwards the event and payload to subscribers.
     *
     * @return void
     */
    public function test_dispatch_forwards_event_and_payload(): void
    {
        $received = array();
        add_action(
            DomainEvents::ORDER_CREATED,
            static function (array $payload) use (&$received): void {
                $received[] = $payload;
            }
        );

        DomainEvents::dispatch(DomainEvents::ORDER_CREATED, array( 'id' => 7 ));

        $this->assertSame(array( array( 'id' => 7 ) ), $received);
    }

    /**
     * Test that dispatch without subscribers does not error.
     *
     * @return void
     */
    public function test_dispatch_without_subscribers_is_safe(): void
    {
        DomainEvents::dispatch(DomainEvents::CART_UPDATED);

        $this->assertFalse(has_action(DomainEvents::CART_UPDATED));
    }
}
