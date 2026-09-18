<?php

declare(strict_types=1);

namespace SmoothRestaurant\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Architecture guard: transactional data lives in custom tables, never in
 * postmeta.
 *
 * Scans src/Domains/ and src/Database/ for postmeta APIs and meta queries
 * and fails on any violation. The sole exception is the optional read-only
 * menu CPT mirror under src/Domains/Menu/, which is display-only and never
 * a source of truth. A second test confines raw wpdb access to
 * src/Database/ so every query flows through BaseRepository.
 */
final class NoPostmetaTest extends TestCase
{
    /**
     * Postmeta APIs forbidden outside the menu CPT mirror.
     *
     * @var list<string>
     */
    private const FORBIDDEN_POSTMETA = [
        'add_post_meta',
        'update_post_meta',
        'get_post_meta',
        'delete_post_meta',
        'add_post_metadata',
        'update_post_metadata',
        'get_post_metadata',
        'delete_post_metadata',
        'get_post_custom',
        'register_post_meta',
        'meta_query',
    ];

    public function test_no_postmeta_in_transactional_domains(): void
    {
        $violations = [];
        foreach ($this->scanRoots(['Domains', 'Database']) as $file) {
            if ($this->isMenuMirror($file)) {
                continue;
            }
            foreach ($this->matchTokens($file, self::FORBIDDEN_POSTMETA) as $hit) {
                $violations[] = $hit;
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Postmeta usage is forbidden for transactional data:' . "\n" . implode("\n", $violations)
        );
    }

    public function test_wpdb_access_is_confined_to_database_layer(): void
    {
        $violations = [];
        foreach ($this->scanRoots(['']) as $file) {
            if (str_contains($file, DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR)) {
                continue;
            }
            foreach ($this->matchTokens($file, ['$wpdb']) as $hit) {
                $violations[] = $hit;
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Raw wpdb access outside src/Database/:' . "\n" . implode("\n", $violations)
        );
    }

    /**
     * @param list<string> $roots Relative to src/.
     * @return list<string> Absolute PHP file paths.
     */
    private function scanRoots(array $roots): array
    {
        $src = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src';
        $files = [];
        foreach ($roots as $root) {
            $dir = '' === $root ? $src : $src . DIRECTORY_SEPARATOR . $root;
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $info) {
                if ($info->isFile() && 'php' === $info->getExtension()) {
                    $files[] = $info->getPathname();
                }
            }
        }
        sort($files);

        return $files;
    }

    private function isMenuMirror(string $file): bool
    {
        return str_contains($file, DIRECTORY_SEPARATOR . 'Domains' . DIRECTORY_SEPARATOR . 'Menu');
    }

    /**
     * @param list<string> $tokens
     * @return list<string> Matches as relative-path:line:token.
     */
    private function matchTokens(string $file, array $tokens): array
    {
        $src = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $lines = file($file);
        if (false === $lines) {
            return [];
        }
        $hits = [];
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
