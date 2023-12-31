<?php

declare(strict_types=1);

return [
    'redis' => [
        'host' => getenv('REDIS_HOST'),
        'port' => (int) getenv('REDIS_PORT'),
    ],
];
