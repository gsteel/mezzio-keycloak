<?php

declare(strict_types=1);

namespace App\Auth;

use Psr\Container\ContainerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

use function assert;
use function is_array;

final class KeycloakProviderFactory
{
    public function __invoke(ContainerInterface $container): Keycloak
    {
        $config = $container->get('config');
        assert(is_array($config));
        $options = $config['keycloak'] ?? [];
        assert(is_array($options));

        return new Keycloak($options);
    }
}
