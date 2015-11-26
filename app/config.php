<?php
return [
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
    
    // name for the log channel
    'logChannel' => 'avenue.log',
    
    // database configurations
    'database' => require 'database.php'
];