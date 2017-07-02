<?php

define('COCOPI_FRONTEND', true);

/**
 *  Fix for PHP built in webserver to handle static files correctly
 *
 *  start local webserver: php -S localhost:8080 index.php
 */
if (PHP_SAPI == 'cli-server' && is_file(__DIR__.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

/**
 * bootstrap Cockpit
 */
$adminfolder = 'cockpit';

include_once(__DIR__."/{$adminfolder}/bootstrap.php");

/**
 * start the app engine
 */
copi::run();
