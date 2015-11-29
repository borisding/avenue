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
     * When ID is not null, update method will be invoked instead.
     * 
     * @param mixed $id
     */
    public function save($id = null);
    
    /**
     * Remove record based on the passed in ID value.
     */
    public function remove($id);
    
    /**
     * Remove all records of the table.
     */
    public function removeAll();
}