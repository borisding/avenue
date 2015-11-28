<?php
namespace Avenue\Database;

interface PdoAdapterInterface
{
    /**
     * Querying database by preparing sql statement.
     * 
     * @param mixed $sql
     */
    public function cmd($sql);
    
    /**
     * Execute the prepared statement.
     */
    public function run();
    
    /**
     * Bind SQL parameters, default is bind data as value.
     * 
     * @param mixed $key
     * @param mixed $value
     * @param boolean $reference
     */
    public function bind($key, $value, $reference = false);
    
    /**
     * Bind SQL parameters in batch by passing key/value pair(s).
     * 
     * @param array $params
     * @param boolean $reference
     */
    public function batch(array $params = [], $reference = false);
    
    /**
     * Begin the transaction query.
     */
    public function begin();
    
    /**
     * End transaction by commiting.
     */
    public function end();
    
    /**
     * Get last inserted ID.
     */
    public function getInsertedId();
}