<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Ensures config/app.php resolves application name from APP_NAME.
 */
final class AppNameConfigTest extends TestCase
{
    private string|false $previousAppName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousAppName = getenv('APP_NAME');
    }

    protected function tearDown(): void
    {
        if ($this->previousAppName === false) {
            putenv('APP_NAME');
        } else {
            putenv('APP_NAME=' . $this->previousAppName);
        }
        parent::tearDown();
    }

    public function test_app_name_defaults_to_durin_app_without_env(): void
    {
        putenv('APP_NAME');

        $config = $this->loadAppConfig();

        $this->assertSame('durin-app', $config['name']);
    }

    public function test_app_name_resolves_from_app_name_env(): void
    {
        putenv('APP_NAME=billing-api');

        $config = $this->loadAppConfig();

        $this->assertSame('billing-api', $config['name']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadAppConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = include dirname(__DIR__, 2) . '/config/app.php';

        return $config;
    }
}
