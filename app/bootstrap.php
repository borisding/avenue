<?php
$app = new \Avenue\App();

// include application services.
require_once AVENUE_APP_DIR. '/services.php';

// include error handling.
require_once AVENUE_APP_DIR. '/errors.php';

// include application routes
require_once AVENUE_APP_DIR. '/routes.php';

// include application view helpers.
require_once AVENUE_APP_DIR. '/views/helpers.php';

// set timezone and rendering application output
$app->setTimezone()->run();