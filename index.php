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

// built-in PHP server request URI handling
// let static file(s) can be recognized and output as is
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (is_file(AVENUE_ROOT_DIR . $path)) {
        return false;
    }
}

// check if `pdo` extension is available
if (!extension_loaded('pdo')) {
    exit('PDO PHP extension is required!');
}

// check if `mbstring` extension is available
if (!extension_loaded('mbstring')) {
    exit('Mbstring PHP extension is required!');
}

// check if `openssl` extension is available
if (!extension_loaded('openssl')) {
    exit('OpenSSL PHP extension is required!');
}

// path to vendor's autoload file
$autoload = AVENUE_VENDOR_DIR. '/autoload.php';

if (!file_exists($autoload)) {
    exit('Autoload file was not found in vendor directory!');
}

// include vendor's autoload file
require_once $autoload;

// include bootstrap file, where app started
require_once AVENUE_APP_DIR . '/bootstrap.php';