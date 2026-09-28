<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Neutral application root contract (preset applied later via durin init).
 */
final class NeutralRootTest extends TestCase
{
    public function test_does_not_claim_minimal_preset(): void
    {
        $yaml = (string) file_get_contents(dirname(__DIR__, 2) . '/durin.yaml');
        $this->assertStringContainsString('preset: uninitialized', $yaml);
        $this->assertStringNotContainsString('preset: minimal', $yaml);
    }

    public function test_has_no_preset_specific_structure(): void
    {
        $root = dirname(__DIR__, 2);
        $this->assertFileDoesNotExist($root . '/src/Kernel.php');
        $this->assertDirectoryDoesNotExist($root . '/src/Http');
        $this->assertDirectoryDoesNotExist($root . '/src/Domain');
        $this->assertDirectoryDoesNotExist($root . '/src/Jobs');

        $api = (string) file_get_contents($root . '/routes/api.php');
        $this->assertStringNotContainsString('/api/health', $api);
        $this->assertStringContainsString('durin init', $api);
    }

    public function test_keeps_universal_bootstrap_pieces(): void
    {
        $root = dirname(__DIR__, 2);
        $this->assertFileExists($root . '/composer.json');
        $this->assertFileExists($root . '/config/app.php');
        $this->assertFileExists($root . '/.env.example');
        $this->assertFileExists($root . '/public/index.php');
        $this->assertDirectoryExists($root . '/var/cache');
        $this->assertDirectoryExists($root . '/var/runtime');

        $config = (string) file_get_contents($root . '/config/app.php');
        $this->assertStringContainsString("getenv('APP_NAME')", $config);
        $this->assertStringContainsString('DiscoveryServiceProvider', $config);
    }

    public function test_public_index_requires_init(): void
    {
        $index = (string) file_get_contents(dirname(__DIR__, 2) . '/public/index.php');
        $this->assertStringContainsString('durin init', $index);
        $this->assertStringNotContainsString('new Kernel()', $index);
    }

    public function test_readme_documents_create_project_and_init(): void
    {
        $readme = (string) file_get_contents(dirname(__DIR__, 2) . '/README.md');
        $this->assertStringContainsString(
            'composer create-project ereborcodeforge/durin-app',
            $readme
        );
        $this->assertStringContainsString('vendor/bin/durin init', $readme);
    }
}
