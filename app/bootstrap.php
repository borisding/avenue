<?php
$app = new \Avenue\App();

// include application service file.
// any new services should be added into this service file.
require_once AVENUE_APP_DIR. '/service.php';

// include application route(s)
// any new routes should be added into this route file.
require_once AVENUE_APP_DIR. '/route.php';

// rendering application's output
$app->render();