<?php

declare(strict_types=1);

namespace App\Handler;

use App\Value\HttpRedirect;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
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
use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;

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
        if (! isset($query['code'])) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            $session->set('OAuth2State', $this->provider->getState());

            return new RedirectResponse(
                $authorizationUrl,
                HttpRedirect::Temporary->value,
            );
        }

        $state = $query['state'] ?? null;
        if (! is_string($state) || $state === '' || $state !== $session->get('OAuth2State')) {
            $session->unset('OAuth2State');

            throw new RuntimeException('Invalid session state');
        }

        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $query['code'],
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Access token retrieval failed', 0, $e);
        }

        try {
            assert($accessToken instanceof AccessToken);
            $user = $this->provider->getResourceOwner($accessToken);

            return new TextResponse(sprintf(
                <<<'TEXT'
                Hey There %s 👋,
                The details we got for you are as follows:
                %s
                TEXT,
                (string) $user->getName(),
                json_encode([
                    'name' => $user->getName(),
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'accessToken' => $accessToken,
                ], JSON_PRETTY_PRINT),
            ));
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to retrieve user details', 0, $e);
        }
    }
}
