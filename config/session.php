<?php
/**
 * Cofigurations for session.
 * This will be used for application session state purposes.
 */
return [
    // type of storage (file, cookie or database)
    'storage' => 'file',
    
    // table name for database storage
    'table' => 'session',
    
    // the save path for file storage
    'path' => '',
    
    // session lifetime in seconds for garbage collector
    'lifetime' => 3600,
    
    // whether to encrypt session's value
    // if storage is cookie, should refer to cookie's encrypt setting instead
    'encrypt' => true
];