<?php
// application error handling based on the environment
$app->service('error', function($exc) use ($app) {
    $environment = $app->config('environment');

    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        $app->response->write('<h3>Something went wrong! Please contact administrator.</h3>');
        $app->response->render();
    } else {
        error_reporting(-1);
        $exc->render();
    }

    return $app;
});