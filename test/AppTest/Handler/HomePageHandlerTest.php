<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class HomePageHandlerTest extends TestCase
{
    public function testReturnsHtmlResponseWhenTemplateRendererProvided(): void
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer
            ->expects(self::once())
            ->method('render')
            ->with('app::home-page')
            ->willReturn('');

        $homePage = new HomePageHandler($renderer);

        $response = $homePage->handle(
            $this->createMock(ServerRequestInterface::class),
        );

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
