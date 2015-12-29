<?php
// application start time
define('AVENUE_START_TIME', microtime(true));

// application memory usage
define('AVENUE_MEMORY_USAGE', memory_get_usage());

// avenue root directory
define('AVENUE_ROOT_DIR', __DIR__);

// avenue app directory
define('AVENUE_APP_DIR', AVENUE_ROOT_DIR . '/app');

// avenue config directory
define('AVENUE_CONFIG_DIR', AVENUE_ROOT_DIR . '/config');

// avenue src directory
define('AVENUE_SRC_DIR', AVENUE_ROOT_DIR . '/src/avenue');

// avenue log directory
define('AVENUE_LOG_DIR', AVENUE_ROOT_DIR . '/log');

// path to 'vendor' directory
define('AVENUE_VENDOR_DIR', AVENUE_ROOT_DIR . '/vendor');

// include vendor's autoload
$PATH_TO_VENDOR_AUTOLOAD_FILE = AVENUE_VENDOR_DIR. '/autoload.php';

if (file_exists($PATH_TO_VENDOR_AUTOLOAD_FILE)) {
    require_once $PATH_TO_VENDOR_AUTOLOAD_FILE;
} else {
    die('Vendor autoload not found!');
}

// include bootstrap file, where app started
require_once AVENUE_APP_DIR . '/bootstrap.php';