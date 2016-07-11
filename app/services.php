<?php
/**
 * File to place other service providers.
 */

// settings for Eloquent database service
$app->container('db', function($app) {
    $environment = $app->getEnvironment();
    $dbConfig = $app->getConfig('database')[$environment];

    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($dbConfig);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
});