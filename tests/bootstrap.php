<?php
$PARENT_DIR = dirname(__DIR__);

// application start time
define('AVENUE_START_TIME', microtime(true));

// avenue root directory
define('AVENUE_ROOT_DIR', $PARENT_DIR);

// path to avenue app directory
define('AVENUE_APP_DIR', AVENUE_ROOT_DIR . '/app');

// path to avenue config directory
define('AVENUE_CONFIG_DIR', AVENUE_ROOT_DIR . '/config');

// path to avenue log directory
define('AVENUE_LOG_DIR', AVENUE_ROOT_DIR . '/log');

// path to avenue public directory
define('AVENUE_PUBLIC_DIR', AVENUE_ROOT_DIR . '/public');

// path to 'vendor' directory
define('AVENUE_VENDOR_DIR', AVENUE_ROOT_DIR . '/vendor');

// inlucde vendor autoloader
$autoloader = require AVENUE_VENDOR_DIR  . '/autoload.php';

// set tests namespace at runtime
$autoloader->setPsr4('Avenue\\Tests\\', __DIR__);