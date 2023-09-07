<?php

declare(strict_types=1);

use App\Auth\AuthenticationAdapter;
use App\Value\HttpRedirect;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Session\RetrieveSession;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

return static function (Mezzio\Application $app, Mezzio\MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');
    $app->get('/auth', App\Handler\AuthHandler::class, 'auth');
    $app->get('/secret', [Mezzio\Authentication\AuthenticationMiddleware::class, App\Handler\SecretHandler::class], 'secret');
    $app->get('/logout', static function (ServerRequestInterface $request) use ($container): ResponseInterface {
        $provider = $container->get(Keycloak::class);
        $session = RetrieveSession::fromRequest($request);
        $token = AuthenticationAdapter::fetchAccessTokenFromSession($session);
        $helper = $container->get(ServerUrlHelper::class);
        $redirectUri = $helper->generate('/');
        $session->clear(); // Clear the session now we're finished with it to prevent stale tokens

        // We may not have a token at all:
        if (! $token) {
            return new RedirectResponse($redirectUri, HttpRedirect::Temporary->value);
        }

        // Logout at keycloak and immediately redirect back to the app home page:
        $logoutUrl = $provider->getLogoutUrl([
            'access_token' => $token,
            'redirect_uri' => $redirectUri,
        ]);

        return new RedirectResponse($logoutUrl, HttpRedirect::Temporary->value);
    }, 'logout');
};
