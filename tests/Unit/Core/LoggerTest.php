<?php

/**
 * Unit tests for the logger contract.
 *
 * Covers: CoreProvider binds LoggerInterface to WpLogger, skipped-provider
 * diagnostics route through a bound test-double logger, and WpLogger writes
 * the [Smooth Restaurant] prefix to error_log.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Contracts\LoggerInterface;
use SmoothRestaurant\Core\Container;
use SmoothRestaurant\Core\Context;
use SmoothRestaurant\Core\Plugin;
use SmoothRestaurant\Core\WpLogger;
use SmoothRestaurant\Providers\MenuProvider;

/**
 * Test-double logger capturing records per level.
 */
class TestLogger implements LoggerInterface
{
    /**
     * Captured records as level:message strings.
     *
     * @var list<string>
     */
    public array $records = array();

    /**
     * Log a debug message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->records[] = 'debug:' . $message;
    }

    /**
     * Log an informational message.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->records[] = 'info:' . $message;
    }

    /**
     * Log a warning.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->records[] = 'warning:' . $message;
    }

    /**
     * Log an error.
     *
     * @param string               $message Log message.
     * @param array<string, mixed> $context Log context.
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->records[] = 'error:' . $message;
    }
}

/**
 * Class LoggerTest
 */
class LoggerTest extends TestCase
{
    /**
     * Previous error_log ini value, restored after each test.
     *
     * @var string|false
     */
    private string|false $previousErrorLog = false;

    /**
     * Set up a clean plugin singleton and stub state for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Plugin::reset();
        Context::reset();
        sr_test_reset_stubs();
        $this->previousErrorLog = ini_get('error_log');
    }

    /**
     * Tear down stub filters and the plugin singleton after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        remove_filter('smooth_restaurant_service_providers');
        if (is_string($this->previousErrorLog)) {
            ini_set('error_log', $this->previousErrorLog);
        }
        sr_test_reset_stubs();
        Context::reset();
        Plugin::reset();
        parent::tearDown();
    }

    /**
     * Test that CoreProvider binds the logger contract to WpLogger.
     *
     * @return void
     */
    public function test_core_provider_binds_logger_interface(): void
    {
        Plugin::instance()->boot();

        $logger = Plugin::instance()->container()->make(LoggerInterface::class);

        $this->assertInstanceOf(WpLogger::class, $logger);
    }

    /**
     * Test that a pre-bound test-double logger captures skipped providers.
     *
     * @return void
     */
    public function test_skipped_provider_routes_through_bound_logger(): void
    {
        $double = new TestLogger();
        Plugin::instance()->container()->instance(LoggerInterface::class, $double);
        add_filter(
            'smooth_restaurant_service_providers',
            static fn (array $list): array => array_merge(
                $list,
                array( 'SmoothRestaurant\\DoesNotExist\\MissingProProvider' )
            )
        );

        Plugin::instance()->boot();

        $skipped = Plugin::instance()->skippedProviders();
        $this->assertArrayHasKey('SmoothRestaurant\\DoesNotExist\\MissingProProvider', $skipped);
        $this->assertContains(MenuProvider::class, Plugin::instance()->providerClasses());
        $this->assertNotSame(array(), $double->records);
        $found = false;
        foreach ($double->records as $record) {
            if (str_contains($record, 'SmoothRestaurant\\DoesNotExist\\MissingProProvider')) {
                $found = true;
                $this->assertStringStartsWith('warning:', $record);
            }
        }
        $this->assertTrue($found, 'Test-double logger should capture the skipped provider.');
    }

    /**
     * Test that a standalone container resolves the default logger.
     *
     * @return void
     */
    public function test_container_resolves_wp_logger_after_core_register(): void
    {
        $container = new Container();
        $container->register(\SmoothRestaurant\Providers\CoreProvider::class);

        $this->assertInstanceOf(WpLogger::class, $container->make(LoggerInterface::class));
    }

    /**
     * Test that WpLogger writes the prefixed line to error_log.
     *
     * @return void
     */
    public function test_wp_logger_writes_prefixed_error_log(): void
    {
        $logFile = tempnam(sys_get_temp_dir(), 'sr-logger-');
        $this->assertIsString($logFile);
        ini_set('error_log', $logFile);

        $logger = new WpLogger();
        $logger->warning('probe message');
        $logger->info('context message', array( 'key' => 'value' ));

        $contents = file_get_contents($logFile);
        unlink($logFile);
        $this->assertIsString($contents);
        $this->assertStringContainsString('[Smooth Restaurant] warning: probe message', $contents);
        $this->assertStringContainsString('[Smooth Restaurant] info: context message {"key":"value"}', $contents);
    }
}
