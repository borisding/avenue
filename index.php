<?php
// application start time
define('AVENUE_START_TIME', microtime(true));

// avenue root directory
define('AVENUE_ROOT_DIR', __DIR__);

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

// include vendor's autoload
$PATH_TO_VENDOR_AUTOLOAD_FILE = AVENUE_VENDOR_DIR. '/autoload.php';

if (!file_exists($PATH_TO_VENDOR_AUTOLOAD_FILE)) {
    die('Vendor autoload not found!');
}

// include vendor's autoloader
require_once $PATH_TO_VENDOR_AUTOLOAD_FILE;

// include bootstrap file, where app started
require_once AVENUE_APP_DIR . '/bootstrap.php';