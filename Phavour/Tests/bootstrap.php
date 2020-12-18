<?php
@ob_start();
define('APP_BASE', __DIR__ . '/app');

if (!function_exists('header_remove')) {
    /** @noinspection PhpUnusedParameterInspection */
    function header_remove($name) {
        return true;
    }
}

require 'autoload.php';

$appPackages = array(
    new Phavour\PhavourTests\DefaultPackage\Package(),
);
