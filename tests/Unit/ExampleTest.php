<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Kernel;
use EreborCodeForge\Durin\Forge\Support\ApplicationPath;
use Erebor\Mithril\Contracts\HttpApplication;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    protected function tearDown(): void
    {
        ApplicationPath::reset();
        parent::tearDown();
    }

    public function test_kernel_can_be_instantiated(): void
    {
        $kernel = new Kernel();
        $this->assertInstanceOf(HttpApplication::class, $kernel);
        $this->assertInstanceOf(Kernel::class, $kernel);
    }

    public function test_application_root_resolves_to_app_not_vendor(): void
    {
        $appRoot = dirname(__DIR__, 2);
        ApplicationPath::setRoot($appRoot);

        $resolved = realpath(ApplicationPath::root()) ?: ApplicationPath::root();
        $expected = realpath($appRoot) ?: $appRoot;
        $this->assertSame($expected, $resolved);

        $forgeVendor = realpath($appRoot . '/vendor/ereborcodeforge/durins-forge');
        if (is_string($forgeVendor)) {
            $this->assertNotSame($forgeVendor, $resolved);
        }
    }
}
