<?php

/**
 * Architecture guard: boot() context discipline.
 *
 * Every concrete provider in src/Providers/ either overrides boot() with an
 * early context bail (isBackendRequest/isAdmin/isDoingCron/isDoingRest), or
 * inherits the no-op default, or declares contexts() as ['all'] (Core and
 * Database, which intentionally participate on every request). Boot bodies
 * are read via reflection and scanned for the bail helpers.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class BootDisciplineTest
 */
final class BootDisciplineTest extends TestCase
{
    /**
     * Context-bail helpers a boot() override must call first.
     *
     * @var list<string>
     */
    private const BAIL_HELPERS = array(
        'isBackendRequest',
        'isAdmin',
        'isDoingCron',
        'isDoingRest',
    );

    /**
     * Test that every provider boot is context-disciplined.
     *
     * @return void
     */
    public function test_boot_is_context_disciplined(): void
    {
        $violations = array();
        foreach ($this->providerClasses() as $class) {
            if ($this->isDisciplined($class)) {
                continue;
            }

            $method       = new \ReflectionMethod($class, 'boot');
            $violations[] = sprintf(
                '%s::boot():%d: overrides boot() without an early context bail and without contexts() === [\'all\'].',
                $class,
                $this->methodStartLine($method)
            );
        }

        $this->assertSame(
            array(),
            $violations,
            'Undisciplined provider boot():' . "\n" . implode("\n", $violations)
        );
    }

    /**
     * Whether a provider boot follows the lifecycle discipline.
     *
     * @param class-string<ServiceProvider> $class Provider class.
     * @return bool
     */
    private function isDisciplined(string $class): bool
    {
        $method = new \ReflectionMethod($class, 'boot');

        // Inherits the no-op default: nothing to discipline.
        if (ServiceProvider::class === $method->getDeclaringClass()->getName()) {
            return true;
        }

        // Overrides boot() with an early context bail.
        $body = $this->methodBody($method);
        foreach (self::BAIL_HELPERS as $helper) {
            $pattern = '/(?<![a-zA-Z0-9_\\\\])' . preg_quote($helper, '/') . '\s*\(/';
            if (1 === preg_match($pattern, $body)) {
                return true;
            }
        }

        // Participates on every request by explicit declaration.
        return array( 'all' ) === $class::contexts();
    }

    /**
     * Concrete provider classes in src/Providers/.
     *
     * @return list<class-string<ServiceProvider>>
     */
    private function providerClasses(): array
    {
        $dir     = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Providers';
        $classes = array();
        foreach ((array) glob($dir . DIRECTORY_SEPARATOR . '*.php') as $file) {
            $class = 'SmoothRestaurant\\Providers\\' . basename((string) $file, '.php');
            $this->assertTrue(
                class_exists($class),
                sprintf('Provider file without a matching class: %s', (string) $file)
            );
            $reflection = new \ReflectionClass($class);
            if (! $reflection->isAbstract()) {
                /** @var class-string<ServiceProvider> $class */
                $classes[] = $class;
            }
        }
        sort($classes);

        return $classes;
    }

    /**
     * Source text of a method body.
     *
     * @param \ReflectionMethod $method Method to slice.
     * @return string
     */
    private function methodBody(\ReflectionMethod $method): string
    {
        $file = $method->getFileName();
        $this->assertIsString($file);
        $lines = file($file);
        $this->assertIsArray($lines);
        $start = $method->getStartLine();
        $end   = $method->getEndLine();
        $this->assertIsInt($start);
        $this->assertIsInt($end);

        return implode('', array_slice($lines, $start - 1, $end - $start + 1));
    }

    /**
     * First source line of a method.
     *
     * @param \ReflectionMethod $method Method to locate.
     * @return int
     */
    private function methodStartLine(\ReflectionMethod $method): int
    {
        $start = $method->getStartLine();
        $this->assertIsInt($start);

        return $start;
    }
}
