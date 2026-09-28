<?php

declare(strict_types=1);

/**
 * Neutral Durin application root — run `vendor/bin/durin init` before serving.
 */

$appRoot = dirname(__DIR__);
require $appRoot . '/vendor/autoload.php';

fwrite(STDERR, "This Durin application is not initialized yet.\n");
fwrite(STDERR, "Run: vendor/bin/durin init [--preset=<id>]\n");
exit(1);
