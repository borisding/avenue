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
    
    // hashed key
    'key' => 'change this to your own random string!'
];