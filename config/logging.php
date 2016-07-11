<?php
/**
 * Cofigurations for logging for application logging purposes.
 */

return [
    // log channel for logging
    'channel' => 'avenue.logging',

    // add log handlers here
    'handlers' => [
        new \Monolog\Handler\StreamHandler(AVENUE_LOG_DIR . '/' . date('Y-m-d'). '.log')
    ],

    // add processors here for handling extra data
    'processors' => []
];