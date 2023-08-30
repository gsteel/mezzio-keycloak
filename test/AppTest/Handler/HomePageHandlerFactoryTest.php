<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HomePageHandlerFactoryTest extends TestCase
{
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testFactoryWithTemplate(): void
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(TemplateRendererInterface::class)
            ->willReturn($renderer);

        $factory  = new HomePageHandlerFactory();
        $homePage = $factory($this->container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
