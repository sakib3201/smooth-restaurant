<?php

/**
 * Architecture guard: register() binds ONLY.
 *
 * Scans every concrete provider in src/Providers/ via reflection and fails
 * when a register() method body calls add_action() or add_filter(). Boot(),
 * never register(), owns hooks.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use SmoothRestaurant\Core\ServiceProvider;

/**
 * Class RegisterPurityTest
 */
final class RegisterPurityTest extends TestCase
{
    /**
     * Hook APIs forbidden inside register() bodies.
     *
     * @var list<string>
     */
    private const FORBIDDEN_HOOKS = array(
        'add_action',
        'add_filter',
    );

    /**
     * Test that no provider registers hooks in register().
     *
     * @return void
     */
    public function test_register_binds_only(): void
    {
        $violations = array();
        foreach ($this->providerClasses() as $class) {
            $method = new \ReflectionMethod($class, 'register');
            $body   = $this->methodBody($method);
            foreach (self::FORBIDDEN_HOOKS as $hook) {
                $pattern = '/(?<![a-zA-Z0-9_\\\\])' . preg_quote($hook, '/') . '\s*\(/';
                if (1 === preg_match($pattern, $body)) {
                    $violations[] = sprintf('%s::register():%d:%s', $class, $method->getStartLine(), $hook);
                }
            }
        }

        $this->assertSame(
            array(),
            $violations,
            'Hook registration inside register():' . "\n" . implode("\n", $violations)
        );
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
}
