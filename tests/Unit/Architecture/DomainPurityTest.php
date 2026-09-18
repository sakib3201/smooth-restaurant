<?php

/**
 * Architecture guard: domain cores stay pure.
 *
 * Scans src/Domains/ for persistence globals and fails on any violation.
 * Domain services hold pure logic only: $wpdb access is confined to
 * src/Database/, and options/postmeta reads belong to the platform layer.
 *
 * @package SmoothRestaurant
 */

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Class DomainPurityTest
 */
final class DomainPurityTest extends TestCase
{
    /**
     * Persistence tokens forbidden in src/Domains/.
     *
     * @var list<string>
     */
    private const FORBIDDEN_TOKENS = array(
        '$wpdb',
        'get_option',
        'update_option',
        'get_post_meta',
    );

    /**
     * Test that domain cores touch no persistence globals.
     *
     * @return void
     */
    public function test_domains_stay_pure(): void
    {
        $violations = array();
        foreach ($this->domainFiles() as $file) {
            foreach ($this->matchTokens($file, self::FORBIDDEN_TOKENS) as $hit) {
                $violations[] = $hit;
            }
        }

        $this->assertSame(
            array(),
            $violations,
            'Persistence access inside src/Domains/:' . "\n" . implode("\n", $violations)
        );
    }

    /**
     * Absolute PHP file paths under src/Domains/.
     *
     * @return list<string>
     */
    private function domainFiles(): array
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Domains';
        if (! is_dir($dir)) {
            return array();
        }

        $files    = array();
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $info) {
            if ($info->isFile() && 'php' === $info->getExtension()) {
                $files[] = $info->getPathname();
            }
        }
        sort($files);

        return $files;
    }

    /**
     * @param list<string> $tokens
     * @return list<string> Matches as relative-path:line:token.
     */
    private function matchTokens(string $file, array $tokens): array
    {
        $src   = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $lines = file($file);
        if (false === $lines) {
            return array();
        }

        $hits = array();
        foreach ($lines as $number => $line) {
            foreach ($tokens as $token) {
                $pattern = '/(?<![a-zA-Z0-9_\\\\])' . preg_quote($token, '/') . '(?![a-zA-Z0-9_])/';
                if (1 === preg_match($pattern, $line)) {
                    $hits[] = str_replace($src, '', $file) . ':' . ($number + 1) . ':' . $token;
                }
            }
        }

        return $hits;
    }
}
