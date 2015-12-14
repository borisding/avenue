<?php
/**
 * Cofigurations for cookie.
 * This will be used for application cookie state purposes.
 */
return [
    // cookie's expiration, default 20 min
    'expire' => 1200,
    
    // cookie's path that is available
    'path' => '/',
    
    // domain where cookie is available
    'domain' => null,
    
    // only transmitted on https
    'secure' => false,
    
    // only for http protocol, not allowed for javascript
    'httpOnly' => true,
    
    // encrypt cookie's value
    'encrypt' => true
];