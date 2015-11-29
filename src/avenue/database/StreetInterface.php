<?php
namespace Avenue\Database;

interface StreetInterface
{
    /**
     * Return one row of record.
     * Parameter is optional.
     * 
     * @param mixed $params
     */
    public function findOne($params = null);
    
    /**
     * Should return found records.
     * Parameter is optional.
     * 
     * @param mixed $params
     */
    public function findAll($params = null);
    
    /**
     * Alias method for both create and update.
     * When id is not null, update method will be invoked.
     * 
     * @param mixed $id
     */
    public function save($id = null);
    
    /**
     * Create new record into database.
     * Last inserted ID will be returned.
     */
    public function create();
    
    /**
     * Update record based on the passed in value.
     * 
     * @param mixed $params
     */
    public function update($params = null);
    
    /**
     * Remove record based on the passed in value.
     */
    public function remove($params);
}