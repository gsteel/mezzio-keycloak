<?php

declare(strict_types=1);

namespace App\Cache;

use Psr\Container\ContainerInterface;
use Redis;

use function assert;
use function is_array;
use function is_int;
use function is_string;

final class RedisClientFactory
{
    public function __invoke(ContainerInterface $container): Redis
    {
        $config = $container->get('config');
        assert(is_array($config));
        $host = $config['redis']['host'] ?? null;
        $port = $config['redis']['port'] ?? null;

        assert(is_string($host) && $host !== '');
        assert(is_int($port));

        $client = new Redis();
        $client->connect($host, $port);

        return $client;
    }
}
