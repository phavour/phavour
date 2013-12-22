<?php

spl_autoload_register(function($name) {
    $rawPath = implode(DIRECTORY_SEPARATOR, explode('\\', $name));
    $path = __DIR__ . '/app/src/' . $rawPath . '.php';

    if (file_exists($path)) {
       include $path;
    }
});

require __DIR__ . '/../../vendor/autoload.php';
