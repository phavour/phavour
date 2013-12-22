<?php
@ob_start();
define('APP_BASE', realpath(dirname(__FILE__) . '/../../../'));

if (!function_exists('header_remove')) {
    function header_remove($name) {
        return true;
    }
}

spl_autoload_register(
    function($class)
    {
        $dir = realpath(__DIR__ . '/../../');
        $target = explode('\\', $class);
        $imploded = implode(DIRECTORY_SEPARATOR, $target);
        $path = $dir . DIRECTORY_SEPARATOR . $imploded . '.php';
        if (file_exists($path)) {
            require_once $path;
        } else {
            $dir = realpath(__DIR__ . '/../../../');
            $path = $dir . '/src/' . $imploded . '.php';
            if (file_exists($path)) {
                include $path;
            }
        }
    }
);
