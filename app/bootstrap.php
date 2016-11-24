<?php
/*********************************************************************
 * Create app instance by providing configuration and ID (optional). *
 * Establish handler and core service registries.                    *
 *********************************************************************/

// retrieve app config
$config = require AVENUE_CONFIG_DIR . '/app.php';

// instantiate app by providing config value
$app = new \Avenue\App($config);

/*******************************************************************
 * Respective registered services for application.                 *
 * 3rd party dependencies, app handlers, routes, and view helpers. *
 *******************************************************************/

// include app handlers.
require 'handlers.php';

// include app dependencies.
require 'dependencies.php';

// include app routes
require 'routes.php';

// include app view helpers.
require 'views/helpers.php';

// resolve `routes` service and start running application
$app->resolve('routes')->run();
