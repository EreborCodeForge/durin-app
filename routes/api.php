<?php

declare(strict_types=1);

use Erebor\Mithril\Http\Response;
use Erebor\Mithril\Router;

return function (Router $router): void {
    $router->get('/api/health', static function (): Response {
        return Response::json([
            'status' => 'ok',
        ]);
    });
};
