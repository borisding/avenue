<?php
/**
 * Cofigurations for mcrypt encryption.
 * This will be used for application encryption purposes.
 */

return [
    // mcrypt cipher
    'cipher' => MCRYPT_RIJNDAEL_256,

    // mcrypt mode
    'mode' => MCRYPT_MODE_CBC,

    // random key
    'key' => '<please change this to your own random key!>'
];