<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Generators must mutate only the application tree, never vendor/.
 */
final class GeneratorMutationTest extends TestCase
{
    public function test_make_module_and_usecase_write_only_under_app(): void
    {
        $root = dirname(__DIR__, 2);
        $bin = $root . '/vendor/bin/durin';
        $this->assertFileExists($bin);

        $manifestPath = $root . '/durin.yaml';
        $manifestBackup = (string) file_get_contents($manifestPath);
        $vendorStamp = $this->vendorFingerprint($root);

        $cwd = getcwd();
        chdir($root);
        try {
            $moduleOut = [];
            $moduleCode = 0;
            exec(
                escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bin) . ' make:module Billing 2>&1',
                $moduleOut,
                $moduleCode
            );
            $this->assertSame(0, $moduleCode, implode("\n", $moduleOut));

            $ucOut = [];
            $ucCode = 0;
            exec(
                escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bin)
                . ' make:usecase CreateInvoice --module=Billing 2>&1',
                $ucOut,
                $ucCode
            );
            $this->assertSame(0, $ucCode, implode("\n", $ucOut));

            $this->assertDirectoryExists($root . '/src/Modules/Billing');
            $this->assertFileExists(
                $root . '/src/Modules/Billing/Application/CreateInvoice/CreateInvoice.php'
            );

            $this->assertSame(
                $vendorStamp,
                $this->vendorFingerprint($root),
                'vendor/ must not be mutated by generators'
            );
        } finally {
            $this->removeTree($root . '/src/Modules');
            file_put_contents($manifestPath, $manifestBackup);
            if (is_string($cwd)) {
                chdir($cwd);
            }
        }
    }

    private function vendorFingerprint(string $root): string
    {
        $vendor = $root . '/vendor';
        $this->assertDirectoryExists($vendor);

        $paths = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($vendor, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if ($file->isFile()) {
                $paths[] = $file->getPathname() . ':' . $file->getMTime() . ':' . $file->getSize();
            }
        }
        sort($paths);

        return hash('xxh128', implode("\n", $paths));
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }
}
