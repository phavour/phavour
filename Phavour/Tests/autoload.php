<?php
require __DIR__ . '/../../vendor/autoload.php';

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();

$map = array(
    'Phavour\\PhavourTests\\' => __DIR__ . '/app'
);

foreach ($map as $namespace => $path) {
    $loader->setPsr4($namespace, $path);
}

$loader->register(true);
