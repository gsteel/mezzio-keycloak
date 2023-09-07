<?php

declare(strict_types=1);

namespace App\Handler;

use App\Auth\AuthenticationAdapter;
use App\Value\HttpRedirect;
use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Token\AccessToken;
use Mezzio\Session\RetrieveSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Throwable;

use function assert;
use function is_string;

final readonly class AuthHandler implements RequestHandlerInterface
{
    public function __construct(
        private Keycloak $provider,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = RetrieveSession::fromRequest($request);
        $query = $request->getQueryParams();

        /**
         * There's no `code` query parameter. Redirect to the OAuth server to start authentication:
         */
        if (! isset($query['code'])) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            // Store state, provided by the server so that we can check the value when the user comes back
            $session->set('OAuth2State', $this->provider->getState());

            /** PKCE is not available in the used Keycloak provider dependency */
            // $pkceCode = $this->provider->getPkceCode();
            // Assert::stringNotEmpty($pkceCode);
            // $session->set('OAuthPKCECode', $pkceCode);

            return new RedirectResponse(
                $authorizationUrl,
                HttpRedirect::Temporary->value,
            );
        }

        /**
         * If the state passed back to us in the query doesn't match what the server gave us previously,
         * then abort the flow.
         */
        $state = $query['state'] ?? null;
        if (! is_string($state) || $state === '' || $state !== $session->get('OAuth2State')) {
            $session->unset('OAuth2State');

            throw new RuntimeException('Invalid session state');
        }

        $session->unset('OAuth2State');

        /**
         * Use the authentication code provided by the server to fetch a valid access token
         */
        try {
            /** PKCE is not available in the used Keycloak provider dependency */
            // $pkceCode = $session->get('OAuthPKCECode');
            // Assert::stringNotEmpty($pkceCode);
            // $this->provider->setPkceCode($pkceCode);
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $query['code'],
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Access token retrieval failed', 0, $e);
        }

        /**
         * Save the access token in the session - we're now authenticated
         */
        assert($accessToken instanceof AccessToken);
        AuthenticationAdapter::persistToken($accessToken, $session);

        return new RedirectResponse('/', HttpRedirect::Temporary->value);
    }
}
