<?php
/**
 * Configuration of the application.
 */

return [
    // auto rendering response body once application run
    'autoRender' => true,

    // current application's version
    'appVersion' => '1.0',

    // http version that is used
    'httpVersion' => '1.1',

    // application's timezone
    'timezone' => 'UTC',

    // application's environment mode (development, staging, production)
    'environment' => 'development',

    // default controller to be assigned when @controller param is empty
    'defaultController' => 'default',

    // encryption configuration
    'encryption' => require_once 'encryption.php',

    // database configuration
    'database' => require_once 'database.php',

    // logging configuration
    'logging' => require_once 'logging.php'
];