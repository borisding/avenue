<?php
/**
 * Configuration of both cookie and session.
 *
 */
return [
    'cookie' => [
        // cookie's expiration
        'expire' => 3600,

        // cookie's path that is available
        'path' => '',

        // domain where cookie is available
        'domain' => '',

        // only transmitted on https
        'secure' => false,

        // only for http protocol, not allowed for javascript
        'httpOnly' => true,

        // whether to encrypt cookie value
        'encrypt' => false,

        // this is for signed cookie purpose
        'secret' => '<please change this secret key!>'
    ],
    'session' => [
        // table name for database storage
        'table' => 'session',

        // session lifetime in seconds for garbage collector
        'lifetime' => 3600,

        // whether to encrypt session value
        'encrypt' => false,

        // reading from master
        'readMaster' => true,

        // this is for session csrf token
        'secret' => '<please change this secret key!>'
    ]
];