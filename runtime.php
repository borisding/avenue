<?php
// include vendor's autoload
$autoload = AVENUE_VENDOR_DIR. '/autoload.php';

if (!file_exists($autoload)) {
    exit('Autoload file was not found in vendor directory!');
}

// include vendor's autoload file
require_once $autoload;

// check if `pdo` extension is available
if (!extension_loaded('pdo')) {
    exit('PDO PHP extension is required!');
}

// check if `mbstring` extension is available
if (!extension_loaded('mbstring')) {
    exit('Mbstring PHP extension is required!');
}

// check if `mcrypt` extension is available
if (!extension_loaded('mcrypt')) {
     exit('Mcrypt PHP extension is required!');
}

// built-in PHP server request URI handling
// let static file(s) can be recognized and output as is
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (is_file(AVENUE_ROOT_DIR . $path)) {
        return false;
    }
}