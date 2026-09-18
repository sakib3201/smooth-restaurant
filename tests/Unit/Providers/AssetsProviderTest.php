<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\Container;
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
 * Unit tests for the AssetsProvider asset gate.
 */
final class AssetsProviderTest extends TestCase
{
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

        $provider->forceLoad = true;
        $provider->enqueueFrontend();
        $this->assertSame([AssetsProvider::FRONTEND_SURFACE], $provider->surfaces);
    }

    public function test_enqueue_admin_respects_gate(): void
    {
        $provider = $this->provider();
        $provider->enqueueAdmin();
        $this->assertSame([], $provider->surfaces);

        $provider->forceLoad = true;
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

    private function provider(): TestableAssetsProvider
    {
        return new TestableAssetsProvider(new Container());
    }
}
