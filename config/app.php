<?php

declare(strict_types=1);

return [
    'name' => 'durin-app',
    'env' => getenv('APP_ENV') ?: 'development',
    'providers' => [
        \EreborCodeForge\Durin\Forge\Core\DiscoveryServiceProvider::class,
    ],
];
