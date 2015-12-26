<?php
/**
 * Cofigurations for session.
 * This will be used for application session state purposes.
 */
return [
    // type of storage (file or database)
    'storage' => 'file',
    
    // the save path for file storage
    'path' => '',
    
    // table name for database storage
    'table' => 'session',
    
    // session lifetime in seconds
    'lifetime' => 1200,
    
    // encrypt session's value
    'encrypt' => true
];