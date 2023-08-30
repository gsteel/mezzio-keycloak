<?php

declare(strict_types=1);

namespace App\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

final class SessionCacheFactory
{
    public function __invoke(ContainerInterface $container): CacheItemPoolInterface
    {
        return new RedisAdapter(
            $container->get(Redis::class),
            'Session',
        );
    }
}
