<?php

declare(strict_types=1);

namespace App\Auth;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

final class AuthenticationAdapterFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationAdapter
    {
        return new AuthenticationAdapter(
            $container->get(Keycloak::class),
            $container->get(TemplateRendererInterface::class),
        );
    }
}
