<?php
/**
 * File to place other service providers.
 */

// register monolog dependency for logging
$app->container('log', function($app) {
    $loggingConfig = $app->getConfig('logging');
    $channel = $loggingConfig['channel'];
    $handlers = $loggingConfig['handlers'];
    $processors = $loggingConfig['processors'];

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