<?php
/**
 * Database connection settings based on the environment.
 */

return [
    // db settings for 'development' environment
    'development' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'test',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => ''
    ],

    // db settings for 'production' environment
    'production' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'productdb',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => ''
    ]
];