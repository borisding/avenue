<?php
// application error handling based on the environment
$app->addService('error', function($e) use ($app) {
    $environment = $app->getConfig('environment');

    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        echo '<h3>Something went wrong! Please contact administrator.</h3>';
    } else {
        // TODO
        error_reporting(-1);
        print_r('something wrong.');
    }
});

// default view service
$app->addService('view', function() use ($app) {
    return new \Avenue\View($app);
});