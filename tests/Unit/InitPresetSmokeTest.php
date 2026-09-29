<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use EreborCodeForge\Durin\Core\Mutation\ScaffoldWriter;
use EreborCodeForge\Durin\Forge\Tooling\Init\ApplicationInitializer;
use EreborCodeForge\Durin\Forge\Tooling\Progress\InitProgressReporter;
use EreborCodeForge\Durin\Forge\Tooling\Runtime\EregionConfigurator;
use EreborCodeForge\Durin\Forge\Tooling\Runtime\EregionInstaller;
use EreborCodeForge\Durin\Forge\Tooling\Runtime\RuntimePlan;
use EreborCodeForge\Durin\Forge\Tooling\Runtime\RuntimeProvisioner;
use EreborCodeForge\Durin\Presets\Registry\DefaultPresetRegistryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration: neutral root + Forge init for built-in presets (in-process, no vendor junction).
 */
final class InitPresetSmokeTest extends TestCase
{
    /** @var list<string> */
    private array $roots = [];

    protected function tearDown(): void
    {
        foreach ($this->roots as $root) {
            $this->removeTree($root);
        }
        $this->roots = [];
        parent::tearDown();
    }

    #[DataProvider('httpPresetProvider')]
    public function test_http_init_applies_runtime_and_http_scaffold(string $preset, string $markerPath): void
    {
        $source = dirname(__DIR__, 2);
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'durin_app_init_' . $preset . '_' . uniqid('', true);
        $this->roots[] = $root;
        $this->mirrorNeutralRoot($source, $root);

        ob_start();
        $result = $this->initializer()->initialize(
            $root,
            $preset,
            new InitProgressReporter(jsonl: true),
            skipRuntimeInstall: true,
        );
        $output = (string) ob_get_clean();

        $this->assertSame($preset, $result['preset']);
        $this->assertSame('mithril-http', $result['runtime']->executionRuntime);
        $this->assertSame('eregion', $result['runtime']->supervisor);

        $yaml = (string) file_get_contents($root . '/durin.yaml');
        $this->assertStringContainsString('preset: ' . $preset, $yaml);
        $this->assertStringContainsString('execution: mithril-http', $yaml);
        $this->assertStringContainsString('supervisor: eregion', $yaml);

        $this->assertFileExists($root . '/' . $markerPath);
        $this->assertFileExists($root . '/src/Kernel.php');
        $this->assertFileExists($root . '/routes/web.php');
        $this->assertFileExists($root . '/public/index.php');
        $this->assertFileDoesNotExist($root . '/src/JobKernel.php');

        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
        $this->assertIsArray($composer);
        $this->assertSame('App\\Kernel', $composer['extra']['mithril']['kernel']);
        $this->assertSame('v0.4.0', $composer['extra']['mithril']['eregion']);

        $env = (string) file_get_contents($root . '/.env.example');
        $this->assertStringContainsString('APP_URL=', $env);
        $this->assertStringContainsString('APP_PORT=', $env);
        $this->assertStringContainsString('"type":"complete"', $output);
    }

    public function test_worker_init_has_job_runtime_without_http_residuals(): void
    {
        $source = dirname(__DIR__, 2);
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'durin_app_init_worker_' . uniqid('', true);
        $this->roots[] = $root;
        $this->mirrorNeutralRoot($source, $root);

        ob_start();
        $result = $this->initializer()->initialize(
            $root,
            'worker',
            new InitProgressReporter(jsonl: true),
            skipRuntimeInstall: true,
        );
        ob_end_clean();

        $this->assertSame('worker', $result['preset']);
        $this->assertSame('mithril-job', $result['runtime']->executionRuntime);
        $this->assertNull($result['runtime']->supervisor);

        $yaml = (string) file_get_contents($root . '/durin.yaml');
        $this->assertStringContainsString('preset: worker', $yaml);
        $this->assertStringContainsString('execution: mithril-job', $yaml);
        $this->assertStringNotContainsString('supervisor:', $yaml);

        $this->assertFileExists($root . '/src/JobKernel.php');
        $this->assertFileDoesNotExist($root . '/src/Kernel.php');
        $this->assertDirectoryDoesNotExist($root . '/routes');
        $this->assertDirectoryDoesNotExist($root . '/public');

        $composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
        $this->assertIsArray($composer);
        $this->assertSame('App\\JobKernel', $composer['extra']['mithril']['job_kernel']);
        $this->assertArrayNotHasKey('kernel', $composer['extra']['mithril']);
        $this->assertArrayNotHasKey('eregion', $composer['extra']['mithril']);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function httpPresetProvider(): array
    {
        return [
            ['minimal', 'src/Http/.gitkeep'],
            ['service', 'src/Domain/.gitkeep'],
        ];
    }

    private function initializer(): ApplicationInitializer
    {
        return new ApplicationInitializer(
            engine: (new DefaultPresetRegistryFactory())->engine(),
            writer: new ScaffoldWriter(),
            runtime: new RuntimeProvisioner(
                installer: new class extends EregionInstaller {
                    public function install(string $applicationRoot, bool $force = false): array
                    {
                        return ['path' => '', 'version' => 'test', 'asset' => '', 'action' => 'skipped'];
                    }

                    public function isInstalled(string $applicationRoot): bool
                    {
                        return true;
                    }
                },
                configurator: new class extends EregionConfigurator {
                    public function configure(
                        string $applicationRoot,
                        bool $force = false,
                        ?RuntimePlan $plan = null,
                    ): array {
                        return [];
                    }
                },
                defaultInstallRunner: false,
            ),
        );
    }

    private function mirrorNeutralRoot(string $source, string $target): void
    {
        mkdir($target, 0777, true);
        foreach (['composer.json', 'durin.yaml', 'config', 'src', 'var', '.env.example'] as $item) {
            $from = $source . DIRECTORY_SEPARATOR . $item;
            $to = $target . DIRECTORY_SEPARATOR . $item;
            if (!file_exists($from)) {
                continue;
            }
            if (is_dir($from)) {
                $this->copyTree($from, $to);
            } else {
                copy($from, $to);
            }
        }
    }

    private function copyTree(string $from, string $to): void
    {
        mkdir($to, 0777, true);
        foreach (scandir($from) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $src = $from . DIRECTORY_SEPARATOR . $item;
            $dst = $to . DIRECTORY_SEPARATOR . $item;
            is_dir($src) ? $this->copyTree($src, $dst) : copy($src, $dst);
        }
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            if (is_file($path)) {
                @unlink($path);
            }
            return;
        }
        foreach (scandir($path) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $path . DIRECTORY_SEPARATOR . $item;
            is_dir($full) ? $this->removeTree($full) : @unlink($full);
        }
        @rmdir($path);
    }
}
