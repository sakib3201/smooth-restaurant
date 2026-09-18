<?php

/**
 * Fixed slot allocator fake.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Testing;

use SmoothRestaurant\Contracts\SlotAllocatorInterface;

/**
 * Class FixedSlotAllocator
 *
 * Constructor-configurable `SlotAllocatorInterface` fake with zero
 * WordPress dependencies. Always returns the canned slots; every criteria
 * payload is recorded for assertions.
 */
final class FixedSlotAllocator implements SlotAllocatorInterface
{
    /**
     * Canned slot payloads.
     *
     * @var list<array<string, mixed>>
     */
    private array $slots;

    /**
     * Recorded criteria payloads, in call order.
     *
     * @var list<array<string, mixed>>
     */
    public array $criteriaCalls = array();

    /**
     * Constructor.
     *
     * @param list<array<string, mixed>> $slots Canned slots to return.
     */
    public function __construct(array $slots = array())
    {
        $this->slots = array_values($slots);
    }

    /**
     * Record the criteria and return the canned slots.
     *
     * @param array<string, mixed> $criteria Date, party size, and scoping criteria.
     * @return array<int, array<string, mixed>> Canned slot payloads.
     */
    public function availableSlots(array $criteria): array
    {
        $this->criteriaCalls[] = $criteria;

        return $this->slots;
    }
}
