<?php

/**
 * Unit tests for the DatabaseProvider admin_init resume hook.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Database\MigrationRunner;
use SmoothRestaurant\Providers\DatabaseProvider;

/**
 * Class DatabaseProviderTest
 */
class DatabaseProviderTest extends TestCase
{
    /**
     * Set up a clean plugin singleton and stub state for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Plugin::reset();
        sr_test_reset_stubs();
    }

    /**
     * Tear down stub state and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        sr_test_reset_stubs();
        Plugin::reset();
        parent::tearDown();
    }

    /**
     * Test that boot() hooks the resume callback in the admin context.
     *
     * @return void
     */
    public function test_boot_hooks_resume_in_admin(): void
    {
        sr_test_set_flag('is_admin', true);
        $container = new Container();
        $container->register(DatabaseProvider::class);
        $container->boot();

        $this->assertNotFalse(has_action('admin_init'));
    }

    /**
     * Test that boot() stays hook-free outside the admin context.
     *
     * @return void
     */
    public function test_boot_bails_outside_admin(): void
    {
        $container = new Container();
        $container->register(DatabaseProvider::class);
        $container->boot();

        $this->assertFalse(has_action('admin_init'));
    }

    /**
     * Test that the admin_init callback drains the deferred cursor.
     *
     * @return void
     */
    public function test_admin_init_drains_pending_cursor(): void
    {
        sr_test_set_flag('is_admin', true);
        $container = new Container();
        $container->register(DatabaseProvider::class);
        $runner = new ResumableMigrationRunner();
        $runner->pending = [7, 8];
        $container->instance(MigrationRunner::class, $runner);
        $container->boot();

        // Core hook: prefix sniff does not apply.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        do_action('admin_init');

        $this->assertSame([7, 8], $runner->visited);
        $this->assertSame([], $runner->pending);
    }
}

/**
 * MigrationRunner with an in-memory pending cursor for provider tests.
 */
class ResumableMigrationRunner extends MigrationRunner
{
    /**
     * Deferred site IDs.
     *
     * @var list<int>
     */
    public array $pending = [];

    /**
     * Migrated site IDs, in visit order.
     *
     * @var list<int>
     */
    public array $visited = [];

    /**
     * Constructor with in-memory version storage.
     */
    public function __construct()
    {
        parent::__construct(
            [],
            static fn (): string => '0.0.0',
            static function (string $version): void {
            }
        );
    }

    /**
     * @return list<int>
     */
    protected function pendingSiteIds(): array
    {
        return $this->pending;
    }

    /**
     * @param list<int> $siteIds
     * @return void
     */
    protected function storePendingSiteIds(array $siteIds): void
    {
        $this->pending = array_values($siteIds);
    }

    /**
     * @param int $siteId
     * @return void
     */
    protected function switchToBlog(int $siteId): void
    {
        $this->visited[] = $siteId;
    }

    /**
     * @return void
     */
    protected function restoreBlog(): void
    {
    }
}
