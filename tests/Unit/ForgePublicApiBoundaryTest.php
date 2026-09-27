<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Application-owned PHP may only import Forge classes listed in Forge public-api.md.
 */
final class ForgePublicApiBoundaryTest extends TestCase
{
    public function test_application_forge_imports_are_whitelisted(): void
    {
        $root = dirname(__DIR__, 2);
        $whitelist = $this->forgePublicClassWhitelist($root);
        $this->assertNotEmpty($whitelist);

        foreach ($this->applicationPhpFiles($root) as $path) {
            $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
            $contents = (string) file_get_contents($path);
            foreach ($this->forgeReferences($contents) as $import) {
                $this->assertContains(
                    $import,
                    $whitelist,
                    sprintf("%s imports non-public Forge class:\n%s", $relative, $import)
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function applicationPhpFiles(string $root): array
    {
        $dirs = ['src', 'public', 'config', 'routes'];
        $out = [];
        foreach ($dirs as $dir) {
            $base = $root . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($base)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                    $out[] = $file->getPathname();
                }
            }
        }
        sort($out);

        return $out;
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
     * Collect Forge class references from use statements and FQCN / ::class strings.
     *
     * @return list<string>
     */
    private function forgeReferences(string $php): array
    {
        $found = [];

        preg_match_all(
            '/^use\s+(EreborCodeForge\\\\Durin\\\\Forge\\\\[A-Za-z0-9_\\\\]+)\s*;/m',
            $php,
            $useMatches
        );
        foreach ($useMatches[1] ?? [] as $import) {
            $found[] = $import;
        }

        preg_match_all(
            '/\\\\?(EreborCodeForge\\\\Durin\\\\Forge\\\\[A-Za-z0-9_\\\\]+)::class/',
            $php,
            $classMatches
        );
        foreach ($classMatches[1] ?? [] as $import) {
            $found[] = $import;
        }

        return array_values(array_unique($found));
    }
}
