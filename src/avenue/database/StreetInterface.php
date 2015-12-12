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
     * Between method.
     * Alternative of greater than equal and less than equal.
     * 
     * @param mixed $column
     * @param mixed $first
     * @param mixed $second
     */
    public function whereBetween($column, $first, $second);
    
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
     * Get all the found records in object.
     */
    public function getAllObj();
    
    /**
     * Get all the found records in indexed.
     */
    public function getAllNum();
    
    /**
     * Get all the found records in both associative and indexed.
     */
    public function getAllBoth();
    
    /**
     * Get one record.
     */
    public function getOne();
    
    /**
     * Get one found record in object.
     */
    public function getOneObj();
    
    /**
     * Get a record in indexed.
     */
    public function getOneNum();
    
    /**
     * Get a record in both associative and indexed.
     */
    public function getOneBoth();
    
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
    
    /**
     * Cross join method.
     * 
     * @param mixed $model
     */
    public function crossJoin($model);
    
    /**
     * Natural join method.
     *
     * @param mixed $model
     */
    public function naturalJoin($model);
    
    /**
     * Through join method.
     * 
     * @param mixed $model
     * @param mixed $junction
     * @param mixed $firstId
     * @param mixed $secondId
     */
    public function throughJoin($model, $junction, $firstId, $secondId);
    
    /**
     * One to one method.
     * 
     * @param mixed $model
     * @param mixed $on
     */
    public function hasOne($model, $on);
    
    /**
     * One to many method.
     * 
     * @param mixed $model
     * @param mixed $on
     */
    public function hasMany($model, $on);
    
    /**
     * Get the current model table name.
     */
    public function getTable();
    
    /**
     * Get the current model primary key.
     */
    public function getPk();
    
    /**
     * Get the current model foreign key.
     */
    public function getFk();
}