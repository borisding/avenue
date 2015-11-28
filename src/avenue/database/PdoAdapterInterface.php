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
     * Fetch result set based on the fetch type.
     * 
     * @param mixed $type
     */
    public function fetchAll($type);
    
    /**
     * Fetch one result based on the fetch type.
     * 
     * @param mixed $type
     */
    public function fetchOne($type);
    
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
     * Cancel transaction by rolling back.
     */
    public function cancel();
    
    /**
     * Get the total row count.
     */
    public function getTotalRows();
    
    /**
     * Get last inserted ID.
     */
    public function getInsertedId();
}