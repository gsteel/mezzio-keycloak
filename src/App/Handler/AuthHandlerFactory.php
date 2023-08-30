<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

final class AuthHandlerFactory
{
    public function __invoke(ContainerInterface $container): AuthHandler
    {
        return new AuthHandler(
            $container->get(Keycloak::class),
        );
    }
}
