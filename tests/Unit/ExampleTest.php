<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use EreborCodeForge\Durin\Forge\Support\ApplicationPath;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    protected function tearDown(): void
    {
        ApplicationPath::reset();
        parent::tearDown();
    }

    public function test_config_resolves_app_name_from_environment(): void
    {
        $root = dirname(__DIR__, 2);
        $previous = getenv('APP_NAME');
        putenv('APP_NAME=example-app');
        try {
            /** @var array{name: string} $config */
            $config = require $root . '/config/app.php';
            $this->assertSame('example-app', $config['name']);
        } finally {
            if ($previous === false) {
                putenv('APP_NAME');
            } else {
                putenv('APP_NAME=' . $previous);
            }
        }
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
