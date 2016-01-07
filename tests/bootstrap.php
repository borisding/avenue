<?php
// inlucde vendor autoloader
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// set tests namespace at runtime
$autoloader->setPsr4('Avenue\\Tests\\', __DIR__);

// set timezone
date_default_timezone_set('UTC');