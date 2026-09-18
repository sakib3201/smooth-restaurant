<?php
/**
 * Payment gateway contract.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Contracts;

/**
 * Interface GatewayInterface
 *
 * Implemented by Free gateways (e.g. COD) and additive Pro gateways
 * (e.g. Stripe). Payloads are plain arrays so implementations stay
 * testable without WordPress loaded.
 */
interface GatewayInterface {

	/**
	 * Gateway identifier (e.g. 'cod', 'stripe').
	 *
	 * @return string
	 */
	public function id(): string;

	/**
	 * Charge a payment.
	 *
	 * @param array<string, mixed> $payload Charge payload.
	 * @return array<string, mixed> Result payload.
	 */
	public function charge( array $payload ): array;

	/**
	 * Refund a payment.
	 *
	 * @param array<string, mixed> $payload Refund payload.
	 * @return array<string, mixed> Result payload.
	 */
	public function refund( array $payload ): array;
}
