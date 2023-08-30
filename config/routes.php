<?php

declare(strict_types=1);

use Mezzio\Application;

return static function (Application $app): void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');
    $app->get('/auth', App\Handler\AuthHandler::class, 'auth');
};
