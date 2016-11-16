<?php
/********************************************************************
 * File to place other service providers of 3rd party dependencies. *
 ********************************************************************/

// register monolog dependency for logging
$app->singleton('logger', function($app) {
    extract($app->getConfig('logging'));

    $logger = new \Monolog\Logger($channel);

    // push each assigned handler
    foreach ($handlers as $handler) {
        $logger->pushHandler($handler);
    }

    // push each assigned processor
    foreach ($processors as $processor) {
        $logger->pushProcessor($handler);
    }

    return $logger;
});
