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
    public function findRaw();
    
    /**
     * Return found result via union.
     * 
     * @param array $queries
     * @param mixed $callback
     */
    public function findUnion(array $queries = [], \Closure $callback = null);
    
    /**
     * Alias method for both create and update.
     * When ID is not null, update method will be invoked instead.
     * 
     * @param mixed $id
     */
    public function save($id = null);
    
    /**
     * Create a new table record.
     */
    public function create();
    
    /**
     * Update an existing record.
     *
     * @param mixed $id
     */
    public function update($id);
    
    /**
     * Remove record based on the passed in ID value.
     */
    public function remove($id);
    
    /**
     * Remove all records of the table.
     */
    public function removeAll();
    
    /**
     * With selected table column(s) in the query.
     * 
     * @param array $columns
     */
    public function withColumns(array $columns = []);
}