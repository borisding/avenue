<?php
// avenue root directory
defined('AVENUE_ROOT_DIR') or define('AVENUE_ROOT_DIR', __DIR__);

// avenue app directory
defined('AVENUE_APP_DIR') or define('AVENUE_APP_DIR', AVENUE_ROOT_DIR . '/app');

// path to 'vendor' directory
defined('AVENUE_VENDOR_DIR') or define('AVENUE_VENDOR_DIR', AVENUE_ROOT_DIR . '/vendor');

// include vendor's autoload
$PATH_TO_VENDOR_AUTOLOAD_FILE = AVENUE_VENDOR_DIR. '/autoload.php';

if (file_exists($PATH_TO_VENDOR_AUTOLOAD_FILE)) {
    require_once $PATH_TO_VENDOR_AUTOLOAD_FILE;
} else {
    die('Vendor autoload not found!');
}

// include bootstrap file, where app started
require_once AVENUE_APP_DIR . '/bootstrap.php';