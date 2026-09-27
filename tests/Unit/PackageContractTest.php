<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Composer / package contract for ereborcodeforge/durin-app.
 */
final class PackageContractTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $composer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->composer = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function test_package_identity_and_php_baseline(): void
    {
        $this->assertSame('ereborcodeforge/durin-app', $this->composer['name']);
        $this->assertSame('project', $this->composer['type']);
        $this->assertSame('^8.5', $this->composer['require']['php']);
        $this->assertArrayNotHasKey('repositories', $this->composer);
        $this->assertArrayNotHasKey('bin', $this->composer);
    }

    public function test_requires_durins_forge_only_among_durin_packages(): void
    {
        $require = $this->composer['require'];
        $this->assertArrayHasKey('ereborcodeforge/durins-forge', $require);
        $this->assertSame('^0.1', $require['ereborcodeforge/durins-forge']);

        foreach ([
            'ereborcodeforge/durin-core',
            'ereborcodeforge/durin-presets',
            'ereborcodeforge/durin-architecture',
            'ereborcodeforge/mithrilphp',
            'ereborcodeforge/mazarbul',
        ] as $forbidden) {
            $this->assertArrayNotHasKey($forbidden, $require);
        }
    }

    public function test_app_namespace_points_to_src(): void
    {
        $this->assertSame(
            ['App\\' => 'src/'],
            $this->composer['autoload']['psr-4']
        );
        $this->assertSame(
            ['App\\Tests\\' => 'tests/'],
            $this->composer['autoload-dev']['psr-4']
        );
    }

    public function test_mithril_extra_metadata(): void
    {
        $mithril = $this->composer['extra']['mithril'];
        $this->assertSame('App\\Kernel', $mithril['kernel']);
        $this->assertSame('v0.3.0', $mithril['eregion']);
        $this->assertSame('EreborCodeForge/eregion', $mithril['eregion_repo']);
    }

    public function test_vendor_bin_durin_exists_after_install(): void
    {
        $root = dirname(__DIR__, 2);
        $candidates = [
            $root . '/vendor/bin/durin',
            $root . '/vendor/bin/durin.bat',
        ];
        $found = false;
        foreach ($candidates as $path) {
            if (is_file($path)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'vendor/bin/durin missing after composer install');
    }
}
