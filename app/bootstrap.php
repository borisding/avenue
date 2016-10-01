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
 * Respective registered services for application.
 * 3rd party dependencies, app handlers, routes, and view helpers.
 */

// include app handlers.
require_once 'handlers.php';

// include app dependencies.
require_once 'dependencies.php';

// include app routes
require_once 'routes.php';

// include app view helpers.
require_once 'views/helpers.php';

/**
 * Boot to run application.
 */

$app->run();