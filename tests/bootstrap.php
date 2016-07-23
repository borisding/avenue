<?php
// application start time
defined('AVENUE_START_TIME') or
define('AVENUE_START_TIME', microtime(true));

// avenue root directory
defined('AVENUE_ROOT_DIR') or
define('AVENUE_ROOT_DIR', dirname(__DIR__));

// path to avenue app directory
defined('AVENUE_APP_DIR') or
define('AVENUE_APP_DIR', AVENUE_ROOT_DIR . '/app');

// path to avenue config directory
defined('AVENUE_CONFIG_DIR') or
define('AVENUE_CONFIG_DIR', AVENUE_ROOT_DIR . '/config');

// path to avenue log directory
defined('AVENUE_LOG_DIR') or
define('AVENUE_LOG_DIR', AVENUE_ROOT_DIR . '/log');

// path to avenue public directory
defined('AVENUE_PUBLIC_DIR') or
define('AVENUE_PUBLIC_DIR', AVENUE_ROOT_DIR . '/public');

// path to 'vendor' directory
defined('AVENUE_VENDOR_DIR') or
define('AVENUE_VENDOR_DIR', AVENUE_ROOT_DIR . '/vendor');

// path to tests directory
defined('AVENUE_TESTS_DIR') or
define('AVENUE_TESTS_DIR', AVENUE_ROOT_DIR . '/tests');

// include vendor's autoload
$PATH_TO_VENDOR_AUTOLOAD_FILE = AVENUE_VENDOR_DIR. '/autoload.php';

if (!file_exists($PATH_TO_VENDOR_AUTOLOAD_FILE)) {
    die('Vendor autoload not found!');
}

// check and define custom constant if mcrypt extension is not available
// to avoid throwing error for default constant in class
if (!extension_loaded('mcrypt')) {
    define('MCRYPT_RIJNDAEL_256', '');
    define('MCRYPT_MODE_CBC', '');
}

// include vendor's autoloader
$autoloader = require $PATH_TO_VENDOR_AUTOLOAD_FILE;

// set tests namespace at runtime
$autoloader->addPsr4('Avenue\\Tests\\', __DIR__);