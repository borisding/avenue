<?php
$app = new \Avenue\App();

// include application registry file.
// any new registries should be added into this service file.
require_once AVENUE_APP_DIR. '/registry.php';

// include application route(s)
// any new routes should be added into this route file.
require_once AVENUE_APP_DIR. '/route.php';

// rendering application's output
$app->render();