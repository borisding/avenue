<?php
// application start time
defined('AVENUE_START_TIME') or
define('AVENUE_START_TIME', microtime(true));

// avenue framework version
defined('AVENUE_FRAMEWORK_VERSION') or
define('AVENUE_FRAMEWORK_VERSION', '1.0');

// avenue root directory
defined('AVENUE_ROOT_DIR') or
define('AVENUE_ROOT_DIR', __DIR__);

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

// built-in PHP server request URI handling
// let static file(s) can be recognized and output as is
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (is_file(AVENUE_ROOT_DIR . $path)) {
        return false;
    }
}

// include vendor's autoloader
require_once $PATH_TO_VENDOR_AUTOLOAD_FILE;

// include bootstrap file, where app started
require_once AVENUE_APP_DIR . '/bootstrap.php';