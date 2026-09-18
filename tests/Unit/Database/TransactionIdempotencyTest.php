<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Database\Repositories\TransactionRepository;
use SmoothRestaurant\Tests\Unit\Database\Support\FakeWpdb;

/**
 * Unit tests for the ledger idempotency-key contract.
 *
 * The transactions table carries a UNIQUE idempotency key with no default so
 * keyless inserts cannot collide on the empty string; writers generate a key
 * via TransactionRepository::generateIdempotencyKey().
 */
final class TransactionIdempotencyTest extends TestCase
{
    public function test_schema_keeps_unique_key_without_empty_default(): void
    {
        $schema = (new TransactionRepository(new FakeWpdb()))->schema();

        $this->assertStringContainsString('UNIQUE KEY idempotency_key (idempotency_key)', $schema);
        $this->assertStringContainsString('idempotency_key varchar(64) NOT NULL', $schema);
        $this->assertStringNotContainsString("idempotency_key varchar(64) NOT NULL DEFAULT ''", $schema);
    }

    public function test_generated_keys_match_hex_format(): void
    {
        $key = TransactionRepository::generateIdempotencyKey();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $key);
    }

    public function test_generated_keys_are_unique(): void
    {
        $keys = [];
        for ($i = 0; $i < 100; $i++) {
            $key = TransactionRepository::generateIdempotencyKey();
            $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $key);
            $keys[] = $key;
        }

        $this->assertCount(100, array_unique($keys));
    }
}
