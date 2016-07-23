<?php
/**
 * Create app instance by injecting the app's configuration.
 * Establish handler and core service registries.
 */

// retrieve app config
$config = require_once AVENUE_CONFIG_DIR . '/app.php';

// instantiate app by providing config value
$app = new \Avenue\App($config);


/**
 * Application's error handler.
 * Error details & stack trace only be displayed in development environment.
 */

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


/**
 * Respective registered services for application.
 * 3rd party dependencies, application routes, and view helpers.
 */

// include application services.
require_once 'services.php';

// include application view helpers.
require_once 'views/helpers.php';

// include application routes
require_once 'routes.php';


/**
 * Boot to run application.
 */

$app->run();