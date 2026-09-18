<?php

/**
 * In-memory payment gateway fake.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Testing;

use SmoothRestaurant\Contracts\GatewayInterface;

/**
 * Class InMemoryGateway
 *
 * Constructor-configurable `GatewayInterface` fake with zero WordPress
 * dependencies. Charge/refund results are canned at construction (or via
 * setters); every call payload is recorded for assertions.
 */
final class InMemoryGateway implements GatewayInterface
{
    /**
     * Recorded charge payloads, in call order.
     *
     * @var list<array<string, mixed>>
     */
    public array $chargeCalls = array();

    /**
     * Recorded refund payloads, in call order.
     *
     * @var list<array<string, mixed>>
     */
    public array $refundCalls = array();

    /**
     * Constructor.
     *
     * @param string               $gatewayId    Gateway identifier.
     * @param array<string, mixed> $chargeResult Canned charge result.
     * @param array<string, mixed> $refundResult Canned refund result.
     */
    public function __construct(
        private string $gatewayId = 'test',
        private array $chargeResult = array( 'status' => 'captured' ),
        private array $refundResult = array( 'status' => 'refunded' )
    ) {
    }

    /**
     * Gateway identifier.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->gatewayId;
    }

    /**
     * Record the payload and return the canned charge result.
     *
     * @param array<string, mixed> $payload Charge payload.
     * @return array<string, mixed> Canned result payload.
     */
    public function charge(array $payload): array
    {
        $this->chargeCalls[] = $payload;

        return $this->chargeResult;
    }

    /**
     * Record the payload and return the canned refund result.
     *
     * @param array<string, mixed> $payload Refund payload.
     * @return array<string, mixed> Canned result payload.
     */
    public function refund(array $payload): array
    {
        $this->refundCalls[] = $payload;

        return $this->refundResult;
    }

    /**
     * Override the canned charge result.
     *
     * @param array<string, mixed> $result New charge result.
     * @return void
     */
    public function setChargeResult(array $result): void
    {
        $this->chargeResult = $result;
    }

    /**
     * Override the canned refund result.
     *
     * @param array<string, mixed> $result New refund result.
     * @return void
     */
    public function setRefundResult(array $result): void
    {
        $this->refundResult = $result;
    }
}
