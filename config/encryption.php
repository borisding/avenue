<?php
/**
 * Cofigurations for encryption.
 * This will be used for application encryption purposes.
 */
return [
    // cipher algorithm
    'cipher' => MCRYPT_RIJNDAEL_256,
    
    // mode
    'mode' => MCRYPT_MODE_CBC,
    
    // random key
    'key' => '<change this your own random key!>'
];