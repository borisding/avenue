<?php
return [
    /**
     * Database settings for development environment.
     */
    'development' => [
        // database connection string
        'dsn' => 'mysql:host=localhost;charset=utf8;dbname=test',
        
        // database user name
        'username' => 'root',
        
        // database user password
        'password' => 'root',
        
        // database table's prefix
        'tablePrefix' => '',
        
        // persistence of connection
        'persist' => true,
        
        // emulate prepares (eg: MySQL)
        'emulate' => false
    ],
    
    /**
     * Database settings for production environment.
     */
    'production' => [
        // database connection string
        'dsn' => 'mysql:host=localhost;charset=utf8;dbname=production',
        
        // database user name
        'username' => 'root',
         
        // database user password
        'password' => 'root',
        
        // database table's prefix
        'tablePrefix' => '',
        
        // persistence of connection
        'persist' => true,
         
        // emulate prepares (eg: MySQL)
        'emulate' => false
    ]
];