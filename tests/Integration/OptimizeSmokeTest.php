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

        $cacheDir = $root . '/var/cache';
        $this->clearCacheArtifacts($cacheDir);

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

        $cacheAfter = $this->listFiles($cacheDir);
        $this->assertNotEmpty($cacheAfter, 'durin optimize should write artifacts under var/cache/');

        $cacheRoot = realpath($cacheDir) ?: $cacheDir;
        foreach ($cacheAfter as $file) {
            $resolved = realpath($file) ?: $file;
            $this->assertStringStartsWith($cacheRoot, $resolved);
            $this->assertStringNotContainsString(
                DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
                $file
            );
        }
    }

    private function clearCacheArtifacts(string $dir): void
    {
        foreach ($this->listFiles($dir) as $file) {
            unlink($file);
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
