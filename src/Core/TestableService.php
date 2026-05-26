<?php
/**
 * Testable service for container resolution tests.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Core;

/**
 * Class TestableService
 *
 * A simple class with no dependencies that can be resolved by the container.
 */
class TestableService {

	/**
	 * A simple value for testing.
	 *
	 * @var string
	 */
	private string $value;

	/**
	 * Constructor.
	 *
	 * @param string $value A value to store.
	 */
	public function __construct( string $value = 'default' ) {
		$this->value = $value;
	}

	/**
	 * Get the stored value.
	 *
	 * @return string
	 */
	public function getValue(): string {
		return $this->value;
	}
}
