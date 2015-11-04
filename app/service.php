<?php
// application error handling based on the environment
$app->addService('error', function($exc) use ($app) {
    $environment = $app->getConfig('environment');

    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        echo '<h3>Something went wrong! Please contact administrator.</h3>';
    } else {
        error_reporting(-1);
        $exc->render();
    }
});