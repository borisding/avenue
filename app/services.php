<?php
/**
 * File to place other service providers.
 */

// register eloquent database service
$app->container('db', function($app) {
    $environment = $app->getEnvironment();
    $dbConfig = $app->getConfig('database')[$environment];

    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($dbConfig);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
});


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