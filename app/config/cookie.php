<?php
/**
 * Cofigurations for cookie.
 * This will be used for application cookie state purposes.
 */
return [
    // cookie's expiration
    'expire' => 3600,
    
    // cookie's path that is available
    'path' => '',
    
    // domain where cookie is available
    'domain' => '',
    
    // only transmitted on https
    'secure' => false,
    
    // only for http protocol, not allowed for javascript
    'httpOnly' => true,
    
    // whether to encrypt cookie value
    'encrypt' => true
];