<?php
// application start time
defined('AVENUE_START_TIME') or
define('AVENUE_START_TIME', microtime(true));

// avenue framework version
defined('AVENUE_FRAMEWORK_VERSION') or
define('AVENUE_FRAMEWORK_VERSION', '1.0');

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
$autoload = AVENUE_VENDOR_DIR. '/autoload.php';

if (!file_exists($autoload)) {
    exit('Autoload file was not found in vendor directory!');
}

// include vendor's autoload file
$autoloader = require $autoload;

// set tests namespace at runtime
$autoloader->addPsr4('Avenue\\Tests\\', __DIR__);

// check if `pdo` extension is available
if (!extension_loaded('pdo')) {
    exit('pdo PHP extension is required!');
}

// check if `mbstring` extension is available
if (!extension_loaded('mbstring')) {
    exit('mbstring PHP extension is required!');
}

// check if `mcrypt` extension is available
if (!extension_loaded('mcrypt')) {
     exit('Mcrypt PHP extension is required!');
}