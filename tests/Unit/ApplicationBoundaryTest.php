<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Ensures application source owns App\ and does not redefine framework namespaces.
 */
final class ApplicationBoundaryTest extends TestCase
{
    public function test_src_php_files_use_app_namespace_only(): void
    {
        $srcRoot = dirname(__DIR__, 2) . '/src';
        $files = $this->phpFiles($srcRoot);
        $this->assertNotEmpty($files);

        $forbidden = [
            'namespace EreborCodeForge\\Durin\\Forge',
            'namespace EreborCodeForge\\Durin\\Core',
            'namespace EreborCodeForge\\Durin\\Presets',
            'namespace EreborCodeForge\\Durin\\Architecture',
        ];

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            $this->assertMatchesRegularExpression(
                '/^namespace App(\\\\[A-Za-z0-9_]+)*;/m',
                $contents,
                $file . ' must declare namespace App'
            );
            foreach ($forbidden as $ns) {
                $this->assertStringNotContainsString($ns, $contents, $file);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $dir): array
    {
        $out = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                $out[] = $file->getPathname();
            }
        }

        return $out;
    }
}
