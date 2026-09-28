<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class OptimizeSmokeTest extends TestCase
{
    public function test_vendor_bin_durin_is_available_for_optimize(): void
    {
        $root = dirname(__DIR__, 2);
        $this->assertFileExists($root . '/vendor/bin/durin');
    }
}
