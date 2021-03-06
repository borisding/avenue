<?php
/***********************************************
 * Application entry script and bootstrapping. *
 ***********************************************/

// check if running via built in server
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (is_file(__DIR__ . $path)) {
        return false;
    }
}

// include entry script configuration
require __DIR__ . '/config/entry.php';

// include bootstrap file, where app started
require AVENUE_APP_DIR . '/bootstrap.php';
