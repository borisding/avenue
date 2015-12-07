<?php
namespace Avenue\Database;

interface StreetInterface
{
    /**
     * Select statement.
     * 
     * @param array $columns
     */
    public function find(array $columns = []);
    
    /**
     * Return one row of found record.
     * 
     * @param array $columns
     */
    public function findOne(array $columns = []);
    
    /**
     * Return all found records.
     * 
     * @param array $columns
     */
    public function findAll(array $columns = []);
    
    /**
     * Select distinct statement.
     * 
     * @param array $columns
     */
    public function findDistinct(array $columns);
    
    /**
     * Return all record(s) for union sql statements.
     * 
     * @param array $objects
     */
    public function findUnion(array $objects);
    
    /**
     * Where statemet condition.
     */
    public function where();
    
    /**
     * AND where condition.
     */
    public function andWhere();
    
    /**
     * OR where condition.
     */
    public function orWhere();
    
    /**
     * Where in statement.
     * 
     * @param mixed $column
     * @param array $values
     */
    public function whereIn($column, array $values);
    
    /**
     * Where not in statement.
     * 
     * @param mixed $column
     * @param array $values
     */
    public function whereNotIn($column, array $values);
    
    /**
     * Group by statement for column.
     * 
     * @param array $columns
     */
    public function groupBy(array $columns);
    
    /**
     * Having statement.
     */
    public function having();
    
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
     * Join statement based on type.
     * 
     * @param mixed $model
     * @param mixed $on
     * @param string $type
     */
    public function join($model, $on, $type = 'inner');
    
    /**
     * Alias of inner join.
     * 
     * @param mixed $model
     * @param mixed $on
     */
    public function innerJoin($model, $on);
    
    /**
     * Alias of left join.
     *
     * @param mixed $model
     * @param mixed $on
     */
    public function leftJoin($model, $on);
    
    /**
     * Alias of right join.
     *
     * @param mixed $model
     * @param mixed $on
     */
    public function rightJoin($model, $on);
}