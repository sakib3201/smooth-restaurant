<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Providers\AssetsProvider;

/**
 * AssetsProvider with scripted context seams for unit tests.
 */
class TestableAssetsProvider extends AssetsProvider
{
    /** @var list<string> */
    public array $hooks = [];

    /** @var list<string> */
    public array $surfaces = [];

    public bool $forceLoad = false;

    public bool $forceCron = false;

    private string $fixtureDir = '';

    public function withFixtureDir(string $dir): self
    {
        $this->fixtureDir = $dir;

        return $this;
    }

    protected function addHook(string $hook, callable $callback): void
    {
        $this->hooks[] = $hook;
    }

    protected function detectSmoothContext(): bool
    {
        return $this->forceLoad;
    }

    protected function isDoingCron(): bool
    {
        return $this->forceCron;
    }

    protected function buildDir(): string
    {
        return '' !== $this->fixtureDir ? $this->fixtureDir : parent::buildDir();
    }

    protected function enqueueSurface(string $surface): void
    {
        $this->surfaces[] = $surface;
    }

    /**
     * @return array{dependencies: list<string>, version: string}|null
     */
    public function exposedManifest(string $surface): ?array
    {
        return $this->manifestData($surface);
    }
}

/**
 * AssetsProvider with call counting for memoization assertions.
 */
class CountingAssetsProvider extends TestableAssetsProvider
{
    public int $detectCalls = 0;

    public int $buildCalls = 0;

    protected function detectSmoothContext(): bool
    {
        ++$this->detectCalls;

        return $this->forceLoad;
    }

    protected function buildDir(): string
    {
        ++$this->buildCalls;

        return __DIR__ . '/Fixtures';
    }
}

/**
 * Unit tests for the AssetsProvider asset gate.
 */
final class AssetsProviderTest extends TestCase
{
    /**
     * Clear per-request memoization before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        AssetsProvider::resetMemo();
    }

    /**
     * Clear memoization and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        AssetsProvider::resetMemo();
        Plugin::reset();
        parent::tearDown();
    }

    public function test_register_adds_no_hooks(): void
    {
        $provider = $this->provider();
        $container = new Container();

        $provider->register($container);

        $this->assertSame([], $provider->hooks);
    }

    public function test_register_binds_provider_in_container(): void
    {
        $provider = $this->provider();
        $container = new Container();
        $provider->register($container);

        $this->assertSame($provider, $container->make(AssetsProvider::class));
    }

    public function test_boot_registers_enqueue_hooks(): void
    {
        $provider = $this->provider();

        $provider->boot(new Container());

        $this->assertSame(['wp_enqueue_scripts', 'admin_enqueue_scripts'], $provider->hooks);
    }

    public function test_boot_bails_during_cron(): void
    {
        $provider = $this->provider();
        $provider->forceCron = true;

        $provider->boot(new Container());

        $this->assertSame([], $provider->hooks);
    }

    public function test_should_not_load_by_default(): void
    {
        $this->assertFalse($this->provider()->shouldLoad());
    }

    public function test_should_load_in_smooth_context(): void
    {
        $provider = $this->provider();
        $provider->forceLoad = true;

        $this->assertTrue($provider->shouldLoad());
    }

    public function test_enqueue_frontend_respects_gate(): void
    {
        $provider = $this->provider();
        $provider->enqueueFrontend();
        $this->assertSame([], $provider->surfaces);

        // The gate is memoized per request: a new request re-evaluates it.
        $provider->forceLoad = true;
        AssetsProvider::resetMemo();
        $provider->enqueueFrontend();
        $this->assertSame([AssetsProvider::FRONTEND_SURFACE], $provider->surfaces);
    }

    public function test_enqueue_admin_respects_gate(): void
    {
        $provider = $this->provider();
        $provider->enqueueAdmin();
        $this->assertSame([], $provider->surfaces);

        // The gate is memoized per request: a new request re-evaluates it.
        $provider->forceLoad = true;
        AssetsProvider::resetMemo();
        $provider->enqueueAdmin();
        $this->assertSame([AssetsProvider::ADMIN_SURFACE], $provider->surfaces);
    }

    public function test_manifest_missing_returns_null(): void
    {
        $this->assertNull($this->provider()->exposedManifest('frontend'));
    }

    public function test_manifest_reads_asset_php_dependencies(): void
    {
        $provider = $this->provider()->withFixtureDir(__DIR__ . '/Fixtures');

        $manifest = $provider->exposedManifest('frontend');

        $this->assertSame(['wp-element'], $manifest['dependencies'] ?? null);
        $this->assertSame('abc123', $manifest['version'] ?? null);
    }

    public function test_global_gate_function_is_available(): void
    {
        $this->assertTrue(function_exists('smooth_should_load'));
        $this->assertFalse(smooth_should_load());
    }

    public function test_should_load_memoizes_gate_per_request(): void
    {
        $provider = new CountingAssetsProvider(new Container());
        $provider->forceLoad = true;

        $this->assertTrue($provider->shouldLoad());

        // Flip the underlying context: the memoized verdict wins.
        $provider->forceLoad = false;
        $this->assertTrue($provider->shouldLoad());
        $this->assertSame(1, $provider->detectCalls);

        AssetsProvider::resetMemo();
        $this->assertFalse($provider->shouldLoad());
        $this->assertSame(2, $provider->detectCalls);
    }

    public function test_manifest_data_cached_per_surface(): void
    {
        $provider = new CountingAssetsProvider(new Container());

        $first  = $provider->exposedManifest('frontend');
        $second = $provider->exposedManifest('frontend');

        $this->assertSame(['wp-element'], $first['dependencies'] ?? null);
        $this->assertSame($first, $second);
        $this->assertSame(1, $provider->buildCalls);

        AssetsProvider::resetMemo();
        $provider->exposedManifest('frontend');
        $this->assertSame(2, $provider->buildCalls);
    }

    public function test_should_load_global_uses_bound_singleton(): void
    {
        Plugin::reset();
        $stub             = $this->provider();
        $stub->forceLoad = true;
        Plugin::instance()->container()->instance(AssetsProvider::class, $stub);

        $this->assertTrue(AssetsProvider::shouldLoadGlobal());

        $stub->forceLoad = false;
        AssetsProvider::resetMemo();
        $this->assertFalse(AssetsProvider::shouldLoadGlobal());
    }

    public function test_should_load_global_falls_back_when_unbooted(): void
    {
        Plugin::reset();

        $this->assertFalse(Plugin::instance()->container()->has(AssetsProvider::class));
        $this->assertFalse(AssetsProvider::shouldLoadGlobal());
    }

    private function provider(): TestableAssetsProvider
    {
        return new TestableAssetsProvider(new Container());
    }
}
