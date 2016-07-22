<?php
/**
 * Application error handling.
 * Error details & stack trace only be displayed in development environment.
 */
$app->container('errors', function($app) {
    $environment = $app->getEnvironment();
    $status = $app->response->getStatusCode();

    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        $message = ($status === 404) ? 'Page not found.' : 'Sorry! Something went wrong.';
        $app->response->write($message);
        $app->response->render();
    } else {
        error_reporting(-1);
        $app->exception()->render();
    }

    return $app;
});