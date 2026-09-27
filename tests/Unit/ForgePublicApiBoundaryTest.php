<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Kernel + public entry may only import Forge classes listed in Forge public-api.md.
 */
final class ForgePublicApiBoundaryTest extends TestCase
{
    public function test_kernel_and_public_index_forge_imports_are_whitelisted(): void
    {
        $root = dirname(__DIR__, 2);
        $whitelist = $this->forgePublicClassWhitelist($root);
        $this->assertNotEmpty($whitelist);

        $paths = [
            $root . '/src/Kernel.php',
            $root . '/public/index.php',
        ];

        foreach ($paths as $path) {
            $imports = $this->forgeUseImports((string) file_get_contents($path));
            foreach ($imports as $import) {
                $this->assertContains(
                    $import,
                    $whitelist,
                    sprintf('%s imports non-public Forge class %s', basename($path), $import)
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function forgePublicClassWhitelist(string $appRoot): array
    {
        $doc = $appRoot . '/vendor/ereborcodeforge/durins-forge/docs/public-api.md';
        $this->assertFileExists($doc);

        $contents = (string) file_get_contents($doc);
        preg_match_all(
            '/`?(EreborCodeForge\\\\Durin\\\\Forge\\\\[A-Za-z0-9_\\\\]+)`?/',
            $contents,
            $matches
        );

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * @return list<string>
     */
    private function forgeUseImports(string $php): array
    {
        preg_match_all(
            '/^use\s+(EreborCodeForge\\\\Durin\\\\Forge\\\\[A-Za-z0-9_\\\\]+)\s*;/m',
            $php,
            $matches
        );

        return array_values(array_unique($matches[1] ?? []));
    }
}
