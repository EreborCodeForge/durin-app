<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class DoctorSmokeTest extends TestCase
{
    public function test_durin_doctor_exits_zero(): void
    {
        $root = dirname(__DIR__, 2);
        $bin = $this->durinBin($root);
        $this->assertNotNull($bin, 'vendor/bin/durin not found');

        $cwd = getcwd();
        chdir($root);
        try {
            $out = [];
            $code = 0;
            exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bin) . ' doctor 2>&1', $out, $code);
            $joined = implode("\n", $out);
            $this->assertSame(0, $code, $joined);
        } finally {
            if (is_string($cwd)) {
                chdir($cwd);
            }
        }
    }

    private function durinBin(string $root): ?string
    {
        $unix = $root . '/vendor/bin/durin';
        if (is_file($unix)) {
            return $unix;
        }

        return null;
    }
}
