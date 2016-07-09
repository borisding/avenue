<?php
/**
 * Configuration of the application.
 */
return [
    // current application's version
    'version' => '1.0',

    // http version that is used
    'http' => '1.1',

    // application's timezone
    'timezone' => 'UTC',

    // application's environment mode (development, staging, production)
    'environment' => 'development',

    // default controller to be assigned when @controller param is empty
    'defaultController' => 'default',

    // database configuration
    'database' => require_once 'database.php',

    // logging configuration
    'logging' => require_once 'logging.php'
];