<?php
@ob_start();
define('APP_BASE', __DIR__ . '/app');

if (!function_exists('header_remove')) {
    function header_remove($name) {
        return true;
    }
}

require 'autoload.php';
