<?php

declare(strict_types=1);

namespace App;

use Laminas\ServiceManager\ServiceManager;
use Mezzio;
use Redis;
use Stevenmaguire;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
final class ConfigProvider
{
    /** @return array<string, mixed> */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'mezzio-session-cache' => [
                'cache_item_pool_service' => Cache\SessionCache::class,
            ],
        ];
    }

    /** @return ServiceManagerConfiguration */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                Cache\SessionCache::class => Cache\SessionCacheFactory::class,
                Handler\AuthHandler::class => Handler\AuthHandlerFactory::class,
                Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,
                Redis::class => Cache\RedisClientFactory::class,
                Stevenmaguire\OAuth2\Client\Provider\Keycloak::class => Auth\KeycloakProviderFactory::class,
            ],
            'aliases' => [
                Mezzio\Session\SessionPersistenceInterface::class => Mezzio\Session\Cache\CacheSessionPersistence::class,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function getTemplates(): array
    {
        return [
            'map' => [
                'layout::default' => __DIR__ . '/../../templates/layout/default.phtml',
                'app::home-page' => __DIR__ . '/../../templates/app/home-page.phtml',
                'error::404' => __DIR__ . '/../../templates/error/404.phtml',
                'error::error' => __DIR__ . '/../../templates/error/error.phtml',
            ],
        ];
    }
}
