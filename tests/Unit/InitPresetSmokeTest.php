<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use EreborCodeForge\Durin\Core\Mutation\ScaffoldWriter;
use EreborCodeForge\Durin\Forge\Tooling\Init\ApplicationInitializer;
use EreborCodeForge\Durin\Forge\Tooling\Progress\InitProgressReporter;
use EreborCodeForge\Durin\Forge\Tooling\Runtime\EregionConfigurator;
use EreborCodeForge\Durin\Forge\Tooling\Runtime\EregionInstaller;
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

    #[DataProvider('presetProvider')]
    public function test_init_applies_preset(string $preset, string $markerPath): void
    {
        $source = dirname(__DIR__, 2);
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'durin_app_init_' . $preset . '_' . uniqid('', true);
        $this->roots[] = $root;
        $this->mirrorNeutralRoot($source, $root);

        $initializer = new ApplicationInitializer(
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
                    public function configure(string $applicationRoot, bool $force = false): array
                    {
                        return [];
                    }
                },
                defaultInstallRunner: false,
            ),
        );

        ob_start();
        $progress = new InitProgressReporter(jsonl: true);
        $result = $initializer->initialize($root, $preset, $progress, skipRuntimeInstall: true);
        $output = (string) ob_get_clean();

        $this->assertSame($preset, $result['preset']);
        $this->assertFileExists($root . '/durin.yaml');
        $yaml = (string) file_get_contents($root . '/durin.yaml');
        $this->assertStringContainsString('preset: ' . $preset, $yaml);
        $this->assertFileExists($root . '/' . $markerPath);
        $this->assertStringContainsString('"type":"complete"', $output);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function presetProvider(): array
    {
        return [
            ['minimal', 'src/Http/.gitkeep'],
            ['service', 'src/Domain/.gitkeep'],
            ['worker', 'src/JobKernel.php'],
        ];
    }

    private function mirrorNeutralRoot(string $source, string $target): void
    {
        mkdir($target, 0777, true);
        foreach (['composer.json', 'durin.yaml', 'config', 'public', 'src', 'routes', 'var', '.env.example'] as $item) {
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
