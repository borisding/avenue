<?php
// application error handling based on the environment
$app->addService('error', function($e) use ($app) {
    $environment = $app->config('environment');

    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        echo '<h3>Something went wrong! Please contact administrator.</h3>';
    } else {
        // TODO
        error_reporting(-1);
        echo 'error.';
    }
});