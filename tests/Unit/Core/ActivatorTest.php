<?php

declare(strict_types=1);

namespace SmoothRestaurant\Core {
    /**
     * Test double for the WordPress flush_rewrite_rules() function.
     *
     * PHP resolves unqualified calls from this namespace to the namespaced
     * function first, so production (where this file is never loaded) keeps
     * calling the global WordPress API untouched.
     *
     * @internal
     */
    function flush_rewrite_rules(): void
    {
        ActivatorTestState::$flushCalls++;
    }

    /**
     * Mutable state for the flush_rewrite_rules() test double.
     *
     * @internal
     */
    final class ActivatorTestState
    {
        public static int $flushCalls = 0;
    }
}

namespace SmoothRestaurant\Tests\Unit\Core {
    use PHPUnit\Framework\TestCase;
    use SmoothRestaurant\Core\Activator;
    use SmoothRestaurant\Core\ActivatorTestState;

    /**
     * Unit tests for the Activator.
     */
    final class ActivatorTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();
            ActivatorTestState::$flushCalls = 0;
        }

        public function test_activate_flushes_rewrite_rules(): void
        {
            Activator::activate();

            $this->assertSame(1, ActivatorTestState::$flushCalls);
        }

        public function test_activate_accepts_network_wide_flag(): void
        {
            Activator::activate(true);

            $this->assertSame(1, ActivatorTestState::$flushCalls);
        }
    }
}
