<?php

declare(strict_types=1);

return [
    'keycloak' => [
        'authServerUrl' => sprintf('https://%s', (string) getenv('KC_HOSTNAME')),
        'realm' => getenv('MK_REALM'),
        'clientId' => getenv('MK_CLIENT_ID'),
        'clientSecret' => getenv('MK_CLIENT_SECRET'),
        'redirectUri' => sprintf('https://%s/auth', (string) getenv('MEZZIO_HOSTNAME')),
        'version' => getenv('KEYCLOAK_VERSION'),
    ],
];
