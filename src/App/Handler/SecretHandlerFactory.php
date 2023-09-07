<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

final class SecretHandlerFactory
{
    public function __invoke(ContainerInterface $container): SecretHandler
    {
        return new SecretHandler($container->get(TemplateRendererInterface::class));
    }
}
