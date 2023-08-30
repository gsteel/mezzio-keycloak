<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

$config = require __DIR__ . '/config.php';

/** @psalm-suppress all */
$dependencies = $config['dependencies'] ?? [];
/** @psalm-suppress all */
$dependencies['services']['config'] = $config;
/** @psalm-suppress all */

return new ServiceManager($dependencies);
