<?php

require __DIR__ . '/../../vendor/autoload.php';

$loader = new \Composer\Autoload\ClassLoader();

$map = array(
    'Phavour\\PhavourTests\\' => __DIR__ . '/app'
);

foreach ($map as $namespace => $path) {
    $loader->setPsr4($namespace, $path);
}

$loader->register(true);
