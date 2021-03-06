<?php
/***************************************
 * Application general configurations. *
 ***************************************/

return [
    // secret used for application, etc encryption
    'secret' => '<IMPORTANT: Please change this secret value to random string!>',

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

    // database configuration
    'database' => require 'services/database.php',

    // logging configuration
    'logging' => require 'services/logging.php',

    // state configuration
    'state' => require 'services/state.php'
];
