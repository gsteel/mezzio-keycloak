<?php

declare(strict_types=1);

namespace App\Handler;

use App\Auth\AuthenticationAdapter;
use DateTimeImmutable;
use DateTimeZone;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Session\RetrieveSession;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webmozart\Assert\Assert;

use function assert;

final readonly class SecretHandler implements RequestHandlerInterface
{
    public function __construct(private TemplateRendererInterface $renderer)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = RetrieveSession::fromRequest($request);
        $expires = $session->get('expires');
        Assert::integer($expires);
        $expires = DateTimeImmutable::createFromFormat('U', (string) $expires);
        assert($expires instanceof DateTimeImmutable);
        $expires = $expires->setTimezone(new DateTimeZone('Europe/London'));
        $variables = [
            'accessTokenExpiryDate' => $expires,
            'user' => $session->get('user'),
            'token' => AuthenticationAdapter::fetchAccessTokenFromSession($session),
        ];

        return new HtmlResponse($this->renderer->render('app::secret', $variables));
    }
}
