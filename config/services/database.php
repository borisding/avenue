<?php
/***********************************************
 * Database settings based on the environment. *
 ***********************************************/

return [
    'development' => [
        // database connection string
        'dsn' => 'mysql:host=localhost;charset=utf8;dbname=test',

        // database user name
        'username' => 'root',

        // database user password
        'password' => 'root',

        // pdo driver option in key/value pairs
        'options' => []
    ],
    'production' => [
        // database connection string
        'dsn' => 'mysql:host=localhost;charset=utf8;dbname=production',

        // database user name
        'username' => 'root',

        // database user password
        'password' => 'root',

        // pdo driver option in key/value pairs
        'options' => []
    ]
];
