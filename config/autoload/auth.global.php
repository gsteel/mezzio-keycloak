<?php

declare(strict_types=1);

use League\OAuth2\Client\Provider\AbstractProvider;

return [
    'keycloak' => [
        'authServerUrl' => sprintf('https://%s', (string) getenv('KC_HOSTNAME')),
        'realm' => getenv('MK_REALM'),
        'clientId' => getenv('MK_CLIENT_ID'),
        'clientSecret' => getenv('MK_CLIENT_SECRET'),
        'redirectUri' => sprintf('https://%s/auth', (string) getenv('MEZZIO_HOSTNAME')),
        'version' => getenv('KEYCLOAK_VERSION'),
        /**
         * PKCE Method has no effect on the @link \Stevenmaguire\OAuth2\Client\Provider\Keycloak provider
         *
         * Because it does not implement the method `getPkceMethod`, nor declare the property `$pkceMethod`
         */
        'pkceMethod' => AbstractProvider::PKCE_METHOD_S256,
    ],
];
