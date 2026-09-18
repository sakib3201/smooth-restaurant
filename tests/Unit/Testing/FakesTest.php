<?php

/**
 * Unit tests for the contract fakes.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Contracts\GatewayInterface;
use SmoothRestaurant\Contracts\NotifierInterface;
use SmoothRestaurant\Contracts\SlotAllocatorInterface;
use SmoothRestaurant\Testing\FixedSlotAllocator;
use SmoothRestaurant\Testing\InMemoryGateway;
use SmoothRestaurant\Testing\NullNotifier;
use SmoothRestaurant\Testing\RecordingNotifier;

/**
 * Class FakesTest
 */
final class FakesTest extends TestCase
{
    /**
     * Test that the gateway fake satisfies its interface and records calls.
     *
     * @return void
     */
    public function test_in_memory_gateway_satisfies_interface(): void
    {
        $gateway = new InMemoryGateway(
            'cod',
            array( 'status' => 'captured', 'id' => 'ch_1' ),
            array( 'status' => 'refunded' )
        );

        $this->assertInstanceOf(GatewayInterface::class, $gateway);
        $this->assertSame('cod', $gateway->id());

        $charge = $gateway->charge(array( 'amount_cents' => 1299 ));
        $this->assertSame(array( 'status' => 'captured', 'id' => 'ch_1' ), $charge);
        $this->assertSame(array( array( 'amount_cents' => 1299 ) ), $gateway->chargeCalls);

        $refund = $gateway->refund(array( 'charge' => 'ch_1' ));
        $this->assertSame(array( 'status' => 'refunded' ), $refund);
        $this->assertSame(array( array( 'charge' => 'ch_1' ) ), $gateway->refundCalls);
    }

    /**
     * Test that the gateway fake results are reconfigurable.
     *
     * @return void
     */
    public function test_in_memory_gateway_results_are_configurable(): void
    {
        $gateway = new InMemoryGateway();
        $gateway->setChargeResult(array( 'status' => 'authorized' ));

        $this->assertSame(array( 'status' => 'authorized' ), $gateway->charge(array()));
    }

    /**
     * Test that the slot fake satisfies its interface and returns canned slots.
     *
     * @return void
     */
    public function test_fixed_slot_allocator_satisfies_interface(): void
    {
        $slots     = array(
            array( 'starts_at' => '2026-09-19T18:00:00', 'seats' => 2 ),
            array( 'starts_at' => '2026-09-19T19:00:00', 'seats' => 4 ),
        );
        $allocator = new FixedSlotAllocator($slots);

        $this->assertInstanceOf(SlotAllocatorInterface::class, $allocator);

        $result = $allocator->availableSlots(array( 'date' => '2026-09-19', 'party_size' => 2 ));
        $this->assertSame($slots, $result);
        $this->assertSame(array( array( 'date' => '2026-09-19', 'party_size' => 2 ) ), $allocator->criteriaCalls);
    }

    /**
     * Test that the null notifier satisfies its interface and drops messages.
     *
     * @return void
     */
    public function test_null_notifier_satisfies_interface(): void
    {
        $notifier = new NullNotifier();

        $this->assertInstanceOf(NotifierInterface::class, $notifier);
        $this->assertSame('null', $notifier->channel());

        $notifier->notify(array( 'to' => 'guest@example.com' ));

        $this->assertSame('null', $notifier->channel());
    }

    /**
     * Test that the recording notifier satisfies its interface and stores messages.
     *
     * @return void
     */
    public function test_recording_notifier_satisfies_interface(): void
    {
        $notifier = new RecordingNotifier('email');

        $this->assertInstanceOf(NotifierInterface::class, $notifier);
        $this->assertSame('email', $notifier->channel());
        $this->assertSame(array(), $notifier->sent());

        $notifier->notify(array( 'to' => 'a@example.com' ));
        $notifier->notify(array( 'to' => 'b@example.com' ));

        $this->assertSame(
            array(
                array( 'to' => 'a@example.com' ),
                array( 'to' => 'b@example.com' ),
            ),
            $notifier->sent()
        );

        $notifier->clear();
        $this->assertSame(array(), $notifier->sent());
    }
}
