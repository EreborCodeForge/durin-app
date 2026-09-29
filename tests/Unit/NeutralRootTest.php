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

    public function test_manifest_is_runtime_unresolved(): void
    {
        $yaml = (string) file_get_contents(dirname(__DIR__, 2) . '/durin.yaml');
        $this->assertStringContainsString('state: unresolved', $yaml);
        $this->assertStringNotContainsString('engine:', $yaml);
        $this->assertStringNotContainsString('server:', $yaml);
        $this->assertStringNotContainsString('execution:', $yaml);
        $this->assertStringNotContainsString('mode: http', $yaml);
        $this->assertStringContainsString('http: false', $yaml);
        $this->assertStringContainsString('messaging: false', $yaml);
    }

    public function test_has_no_preset_specific_structure(): void
    {
        $root = dirname(__DIR__, 2);
        $this->assertFileDoesNotExist($root . '/src/Kernel.php');
        $this->assertFileDoesNotExist($root . '/src/JobKernel.php');
        $this->assertDirectoryDoesNotExist($root . '/src/Http');
        $this->assertDirectoryDoesNotExist($root . '/src/Domain');
        $this->assertDirectoryDoesNotExist($root . '/src/Jobs');
        $this->assertDirectoryDoesNotExist($root . '/routes');
        $this->assertDirectoryDoesNotExist($root . '/public');
    }

    public function test_keeps_universal_bootstrap_pieces(): void
    {
        $root = dirname(__DIR__, 2);
        $this->assertFileExists($root . '/composer.json');
        $this->assertFileExists($root . '/config/app.php');
        $this->assertFileExists($root . '/.env.example');
        $this->assertDirectoryExists($root . '/var/cache');
        $this->assertDirectoryExists($root . '/var/runtime');
        $this->assertDirectoryExists($root . '/storage');

        $config = (string) file_get_contents($root . '/config/app.php');
        $this->assertStringContainsString("getenv('APP_NAME')", $config);
        $this->assertStringContainsString('DiscoveryServiceProvider', $config);

        $env = (string) file_get_contents($root . '/.env.example');
        $this->assertStringContainsString('APP_NAME=', $env);
        $this->assertStringNotContainsString('APP_URL=', $env);
        $this->assertStringNotContainsString('APP_PORT=', $env);
    }

    public function test_readme_documents_create_project_and_init(): void
    {
        $readme = (string) file_get_contents(dirname(__DIR__, 2) . '/README.md');
        $this->assertStringContainsString(
            'composer create-project ereborcodeforge/durin-app',
            $readme
        );
        $this->assertStringContainsString('vendor/bin/durin init', $readme);
        $this->assertStringContainsString('runtime.state: unresolved', $readme);
    }
}
