<?php
$app = new \Avenue\App();

// include error handling.
require_once AVENUE_APP_DIR. '/errors.php';

// include application services.
require_once AVENUE_APP_DIR. '/services.php';

// include application view helpers.
require_once AVENUE_APP_DIR. '/helpers.php';

// include application routes
require_once AVENUE_APP_DIR. '/routes.php';

// rendering application output
$app->render();