<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Semantic parity with the minimal preset bootstrap contract (not byte equality).
 */
final class MinimalPresetParityTest extends TestCase
{
    public function test_durin_yaml_is_minimal_http(): void
    {
        $yaml = (string) file_get_contents(dirname(__DIR__, 2) . '/durin.yaml');
        $this->assertStringContainsString('preset: minimal', $yaml);
        $this->assertStringContainsString('engine: mithril', $yaml);
        $this->assertStringContainsString('server: eregion', $yaml);
        $this->assertStringContainsString('mode: http', $yaml);
        $this->assertStringContainsString('http: true', $yaml);
        $this->assertStringContainsString('modules: false', $yaml);
    }

    public function test_kernel_composes_http_application_kernel(): void
    {
        $kernel = (string) file_get_contents(dirname(__DIR__, 2) . '/src/Kernel.php');
        $this->assertStringContainsString('namespace App;', $kernel);
        $this->assertStringContainsString('final class Kernel implements HttpApplication', $kernel);
        $this->assertStringContainsString(
            'use EreborCodeForge\\Durin\\Forge\\Core\\Http\\HttpApplicationKernel;',
            $kernel
        );
    }

    public function test_public_bootstrap_sets_application_path(): void
    {
        $index = (string) file_get_contents(dirname(__DIR__, 2) . '/public/index.php');
        $this->assertStringContainsString('ApplicationPath::setRoot($appRoot)', $index);
        $this->assertStringContainsString('dirname(__DIR__)', $index);
        $this->assertStringContainsString('new Kernel()', $index);
    }

    public function test_config_and_routes_bootstrap_exist(): void
    {
        $root = dirname(__DIR__, 2);
        $this->assertFileExists($root . '/config/app.php');
        $this->assertFileExists($root . '/routes/api.php');
        $this->assertFileExists($root . '/routes/web.php');

        $config = (string) file_get_contents($root . '/config/app.php');
        $this->assertStringContainsString('DiscoveryServiceProvider', $config);

        $api = (string) file_get_contents($root . '/routes/api.php');
        $this->assertStringContainsString('/api/health', $api);
    }

    public function test_readme_documents_create_project_and_doctor(): void
    {
        $readme = (string) file_get_contents(dirname(__DIR__, 2) . '/README.md');
        $this->assertStringContainsString(
            'composer create-project ereborcodeforge/durin-app',
            $readme
        );
        $this->assertStringContainsString('vendor/bin/durin doctor', $readme);
        $this->assertStringContainsString('vendor/bin/durin dev', $readme);
    }
}
