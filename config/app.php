<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'durin-app',
    'env' => getenv('APP_ENV') ?: 'development',
    'providers' => [
        \EreborCodeForge\Durin\Forge\Core\DiscoveryServiceProvider::class,
    ],
];
