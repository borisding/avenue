<?php
/**
 * Application's handlers.
 */

// error handling via registered error handler
$app->container('errorHandler', function($app) {
    $environment = $app->getEnvironment();
    $status = $app->response->getStatusCode();

    // example of error messages handling based on the environment
    // can modify based on the different context
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
