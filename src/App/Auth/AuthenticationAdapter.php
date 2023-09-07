<?php

declare(strict_types=1);

namespace App\Auth;

use Laminas\Diactoros\Response\HtmlResponse;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\RetrieveSession;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Throwable;
use Webmozart\Assert\Assert;

use function is_array;

/**
 * Authentication Adapter
 *
 * All this adapter does is serialise and un-serialise information in the session, and, refreshes the access token
 * when it has expired. It's also responsible for returning a response for unauthorised requests.
 *
 * The OAuth flow is handled by @see \App\Handler\AuthHandler and is responsible for persisting the token in the session
 * in a way that allows this adapter to fetch it again: @see self::persistToken()
 */
final readonly class AuthenticationAdapter implements AuthenticationInterface
{
    public function __construct(
        private Keycloak $provider,
        private TemplateRendererInterface $renderer,
    ) {
    }

    public function authenticate(ServerRequestInterface $request): UserInterface|null
    {
        $session = RetrieveSession::fromRequest($request);
        $token = self::fetchAccessTokenFromSession($session);
        if (! $token) {
            return null;
        }

        if ($token->hasExpired()) {
            $token = $this->refreshAccessToken($token, $session);
        }

        if (! $token instanceof AccessToken) {
            $session->clear();

            return null;
        }

        return $this->fetchUser($token, $session);
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse(
            $this->renderer->render('error::403'),
            403,
        );
    }

    private function refreshAccessToken(AccessTokenInterface $token, SessionInterface $session): AccessTokenInterface|null
    {
        try {
            $token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => $token->getRefreshToken()]);

            self::persistToken($token, $session);

            return $token;
        } catch (Throwable) {
            return null;
        }
    }

    private function fetchUser(AccessToken $token, SessionInterface $session): User
    {
        /** @psalm-var mixed $user */
        $user = $session->get('user');
        if (! is_array($user)) {
            $user = $this->provider->getResourceOwner($token)->toArray();
            $session->set('user', $user);
        }

        return User::fromArray($user);
    }

    public static function persistToken(AccessTokenInterface $token, SessionInterface $session): void
    {
        $session->set('accessToken', $token->getToken());
        $session->set('expires', $token->getExpires());
        $session->set('refreshToken', $token->getRefreshToken());
        $session->set('idToken', $token->getValues()['id_token'] ?? null);
    }

    public static function fetchAccessTokenFromSession(SessionInterface $session): AccessToken|null
    {
        $accessToken = $session->get('accessToken');
        $expires = $session->get('expires');
        $refreshToken = $session->get('refreshToken');
        $idToken = $session->get('idToken');

        try {
            Assert::string($accessToken);
            Assert::integer($expires);
            Assert::stringNotEmpty($refreshToken);
            Assert::stringNotEmpty($idToken);
        } catch (Throwable) {
            return null;
        }

        return new AccessToken([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires' => $expires,
            'id_token' => $idToken,
        ]);
    }
}
