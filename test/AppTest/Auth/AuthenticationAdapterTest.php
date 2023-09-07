<?php

declare(strict_types=1);

namespace AppTest\Auth;

use App\Auth\AuthenticationAdapter;
use Laminas\Diactoros\ServerRequest;
use League\OAuth2\Client\Token\AccessToken;
use Mezzio\Session\Session;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

use function mktime;

class AuthenticationAdapterTest extends TestCase
{
    private Session $session;
    private ServerRequest $request;
    private Keycloak&MockObject $keycloak;
    private TemplateRendererInterface&MockObject $renderer;
    private AuthenticationAdapter $adapter;

    protected function setUp(): void
    {
        $this->session = new Session([]);
        $this->request = (new ServerRequest())->withAttribute(SessionInterface::class, $this->session);
        $this->keycloak = $this->createMock(Keycloak::class);
        $this->renderer = $this->createMock(TemplateRendererInterface::class);
        $this->adapter = new AuthenticationAdapter($this->keycloak, $this->renderer);
    }

    public function testThatAnAccessTokenCanBePersistedInTheRequest(): void
    {
        $token = AuthenticationAdapter::fetchAccessTokenFromSession($this->session);

        self::assertNull($token);

        $token = new AccessToken([
            'access_token' => 'foo',
            'refresh_token' => 'bar',
            'expires' => mktime(0, 0, 0, 0, 0, 0),
            'id_token' => 'baz',
        ]);

        AuthenticationAdapter::persistToken($token, $this->session);

        $persisted = AuthenticationAdapter::fetchAccessTokenFromSession($this->session);

        self::assertNotNull($persisted);
        self::assertNotSame($token, $persisted);
        self::assertSame($token->getToken(), $persisted->getToken());
        self::assertSame($token->getRefreshToken(), $persisted->getRefreshToken());
        self::assertSame($token->getExpires(), $persisted->getExpires());
    }

    public function testThatAuthenticationWillFailWhenTheSessionDoesNotContainAnAccessToken(): void
    {
        $this->keycloak->expects(self::never())->method(self::anything());
        $result = $this->adapter->authenticate($this->request);
        self::assertNull($result);
    }

    public function testThatTheUnauthorizedResponseWillHaveTheCorrectStatusCode(): void
    {
        $this->keycloak->expects(self::never())->method(self::anything());
        $this->renderer->expects(self::once())->method('render');
        $response = $this->adapter->unauthorizedResponse($this->request);
        self::assertEquals(403, $response->getStatusCode());
    }
}
