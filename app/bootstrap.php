<?php
/**
 * Create app instance.
 * This makes core services, error & exception handlers available.
 */

$app = new \Avenue\App();


/**
 * Application's error handler.
 * Error details & stack trace only be displayed in development environment.
 */

$app->container('errorHandler', function($app) {
    $environment = $app->getEnvironment();
    $status = $app->response->getStatusCode();

    // exmple of error messages handling based on the environment
    // can modify based on the different needs
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


/**
 * Respective registered services for application.
 * 3rd party dependencies, application routes, and view helpers.
 */

// include application services.
require_once AVENUE_APP_DIR. '/services.php';

// include application view helpers.
require_once AVENUE_APP_DIR. '/views/helpers.php';

// include application routes
require_once AVENUE_APP_DIR. '/routes.php';


/**
 * Set application timezone and run.
 */

$app->setTimezone()->run();