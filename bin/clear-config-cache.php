<?php

declare(strict_types=1);

chdir(__DIR__ . '/../');

require 'vendor/autoload.php';

$config = include 'config/config.php';

$cachePath = $config['config_cache_path'] ?? null;

if (! isset($cachePath) || ! is_string($cachePath)) {
    echo 'No configuration cache path found' . PHP_EOL;
    exit(1);
}

if (! file_exists($cachePath)) {
    printf(
        "Configured config cache file '%s' not found%s",
        $cachePath,
        PHP_EOL,
    );
    exit(0);
}

if (unlink($cachePath) === false) {
    printf(
        "Error removing config cache file '%s'%s",
        $cachePath,
        PHP_EOL,
    );
    exit(1);
}

printf(
    "Removed configured config cache file '%s'%s",
    $cachePath,
    PHP_EOL,
);
exit(0);
