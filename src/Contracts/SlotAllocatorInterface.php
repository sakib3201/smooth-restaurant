<?php
/**
 * Slot allocation contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface SlotAllocatorInterface
 *
 * Computes bookable time slots from plain-array criteria. Pro consumes
 * this interface for reads; slot rules stay pure (no $wpdb here).
 */
interface SlotAllocatorInterface {

	/**
	 * List available slots for the given criteria.
	 *
	 * @param array<string, mixed> $criteria Date, party size, and scoping criteria.
	 * @return array<int, array<string, mixed>> Available slot payloads.
	 */
	public function availableSlots( array $criteria ): array;
}
