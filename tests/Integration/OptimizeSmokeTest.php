<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class OptimizeSmokeTest extends TestCase
{
    public function test_optimize_writes_under_var_cache(): void
    {
        $root = dirname(__DIR__, 2);
        $bin = $root . '/vendor/bin/durin';
        $this->assertFileExists($bin);

        $cacheBefore = $this->listFiles($root . '/var/cache');

        $cwd = getcwd();
        chdir($root);
        try {
            $out = [];
            $code = 0;
            exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bin) . ' optimize 2>&1', $out, $code);
            $joined = implode("\n", $out);
            $this->assertSame(0, $code, $joined);
        } finally {
            if (is_string($cwd)) {
                chdir($cwd);
            }
        }

        $cacheAfter = $this->listFiles($root . '/var/cache');
        $this->assertGreaterThan(
            count($cacheBefore),
            count($cacheAfter),
            'durin optimize should write artifacts under var/cache/'
        );

        foreach ($cacheAfter as $file) {
            $this->assertStringStartsWith(
                realpath($root . '/var/cache') ?: ($root . '/var/cache'),
                realpath($file) ?: $file
            );
            $this->assertStringNotContainsString(
                DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
                $file
            );
        }
    }

    /**
     * @return list<string>
     */
    private function listFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $out = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if ($file->isFile() && $file->getFilename() !== '.gitkeep') {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }
}
