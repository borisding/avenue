<?php
// root directory
defined('AVENUE_ROOT_DIR') or define('AVENUE_ROOT_DIR', __DIR__);

// path to 'vendor' directory
defined('AVENUE_VENDOR_DIR') or define('AVENUE_VENDOR_DIR', AVENUE_ROOT_DIR . '/vendor');

// include vendor autoload
$PATH_TO_VENDOR_AUTOLOAD_FILE = AVENUE_VENDOR_DIR. '/autoload.php';

if (file_exists($PATH_TO_VENDOR_AUTOLOAD_FILE)) {
    require_once $PATH_TO_VENDOR_AUTOLOAD_FILE;
} else {
    die('Vendor autoload not found!');
}