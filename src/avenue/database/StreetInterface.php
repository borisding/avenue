<?php
namespace Avenue\Database;

interface StreetInterface
{
    /**
     * Return one row of found record(s).
     */
    public function findOne();
    
    /**
     * Return all found records.
     */
    public function findAll();

    /**
     * Return select raw SQL query.
     */
    public function findQuery();

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