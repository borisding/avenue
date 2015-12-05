<?php
namespace Avenue\Database;

interface StreetInterface
{
    /**
     * Select statement.
     */
    public function find();
    
    /**
     * Return one row of found record(s).
     */
    public function findOne();
    
    /**
     * Return all found records.
     */
    public function findAll();
    
    /**
     * Return all record(s) for union sql statements.
     * 
     * @param array $objects
     */
    public function findUnion(array $objects);
    
    /**
     * Where statemet condition.
     * 
     * @param mixed $column
     * @param mixed $value
     */
    public function where($column, $value);
    
    /**
     * AND where condition.
     * 
     * @param mixed $column
     * @param mixed $value
     */
    public function andWhere($column, $value);
    
    /**
     * OR where condition.
     * 
     * @param mixed $column
     * @param mixed $value
     */
    public function orWhere($column, $value);
    
    /**
     * In statement for multiple values.
     * 
     * @param mixed $column
     * @param array $values
     */
    public function in($column, array $values);
    
    /**
     * Group by statement for column.
     * 
     * @param array $columns
     */
    public function groupBy(array $columns);
    
    /**
     * Order by statement.
     * 
     * @param array $sorting
     */
    public function orderBy(array $sorting);
    
    /**
     * Limit offset statement.
     * 
     * @param mixed $row
     * @param mixed $offset
     */
    public function limit($row, $offset);
    
    /**
     * Get all the found records.
     */
    public function getAll();
    
    /**
     * Get one found record.
     */
    public function getOne();
    
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
    public function column(array $columns = []);
}