<?php
namespace Avenue\Interfaces\Database;

interface CommandInterface
{
    /**
     * Method for prepared statement.
     * Allowed to decide for master or slave.
     *
     * @param mixed $sql
     * @param mixed $slave
     */
    public function cmd($sql, $slave);

    /**
     * Prepare statement for master database.
     *
     * @param mixed $sql
     */
    public function cmdMaster($sql);

    /**
     * Prepare statement for slave database.
     *
     * @param mixed $sql
     */
    public function cmdSlave($sql);

    /**
     * Execute prepared statement.
     */
    public function run();

    /**
     * Execute prepared statement with actual param values.
     *
     * @param array $params
     */
    public function runWith(array $params);

    /**
     * Fetch all records method based on type.
     *
     * @param mixed $type
     */
    public function all($type);

    /**
     * Fetch single record method based on type.
     *
     * @param mixed $type
     */
    public function one($type);

    /**
     * Fetch single record with class behavior.
     *
     * @param mixed $name
     * @param array $ctorArgs
     */
    public function classOne($name, array $ctorArgs);

    /**
     * Fetch multiple records with class behavior.
     *
     * @param mixed $name
     * @param array $ctorArgs
     */
    public function classAll($name, array $ctorArgs);

    /**
     * Fetch a column from the next row.
     *
     * @param integer $number
     */
    public function column($number);

    /**
     * Fetch and get total number of rows.
     */
    public function totalRows();

    /**
     * Bind parameters for prepared statement.
     *
     * @param mixed $key
     * @param mixed $value
     * @param mixed $reference
     */
    public function bind($key, $value, $reference);

    /**
     * Bind parameters for prepared statement in mass.
     *
     * @param array $params
     * @param mixed $reference
     */
    public function batch(array $params, $reference);

    /**
     * Begin transaction.
     */
    public function begin();

    /**
     * End transaction.
     */
    public function end();

    /**
     * Cancel transaction.
     */
    public function cancel();

    /**
     * Get the number of affected rows.
     */
    public function affectedRows();

    /**
     * Get inserted ID.
     */
    public function insertedId();

    /**
     * Debug SQL statement.
     *
     * @param  mixed $sql
     * @param  array  $data
     */
    public function debug($sql, array $data);

    /**
     * Sql select method.
     *
     * @param  mixed $columns
     */
    public function select($columns);

    /**
     * Sql select count method.
     *
     * @param  mixed $columns
     */
    public function selectCount($columns);

    /**
     * Sql select distinct method.
     *
     * @param  mixed $columns
     */
    public function selectDistinct($columns);

    /**
     * Sql from method.
     *
     * @param  mixed $table
     */
    public function from($table);

    /**
     * Sql insert method.
     *
     * @param  mixed $table
     * @param  array  $columns
     */
    public function insert($table, array $columns);

    /**
     * Sql update method.
     *
     * @param  mixed $table
     * @param  array  $columns
     */
    public function update($table, array $columns);

    /**
     * Sql upsert method.
     *
     * @param  mixed $table
     * @param  mixed $pk
     * @param  array  $columns
     */
    public function upsert($table, $pk, array $columns);
    
    /**
     * Sql delete method.
     *
     * @param  mixed $table
     */
    public function delete($table);

    /**
     * Sql where method.
     */
    public function where();

    /**
     * Sql and where method.
     */
    public function andWhere();

    /**
     * Sql or where method.
     */
    public function orWhere();

    /**
     * Sql order by method.
     *
     * @param  mixed  $columns
     */
    public function orderBy($columns);

    /**
     * Sql limit method.
     *
     * @param  mixed $rows
     * @param  mixed $from
     */
    public function limit($rows, $from);

    /**
     * Sql offset method.
     *
     * @param mixed $from
     */
    public function offset($from);

    /**
     * Sql group by method.
     *
     * @param  mixed  $columns
     */
    public function groupBy($columns);

    /**
     * Sql having method.
     *
     * @param  mixed $aggregate
     * @param  mixed $operator
     * @param  mixed $input
     */
    public function having($aggregate, $operator, $input);

    /**
     * Sql union method.
     *
     * @param  array  $sqls
     */
    public function union(array $sqls);

    /**
     * Sql join method.
     *
     * @param  mixed $table
     * @param  array  $on
     */
    public function join($table, array $on);

    /**
     * Sql inner join method.
     *
     * @param  mixed $table
     * @param  array  $on
     */
    public function innerJoin($table, array $on);

    /**
     * Sql left join method.
     *
     * @param  mixed $table
     * @param  array  $on
     */
    public function leftJoin($table, array $on);

    /**
     * Sql right join method.
     *
     * @param  mixed $table
     * @param  array  $on
     */
    public function rightJoin($table, array $on);

    /**
     * Sql full join method.
     *
     * @param  mixed $table
     * @param  array  $on
     */
    public function fullJoin($table, array $on);

    /**
     * Sql like method.
     *
     * @param  mixed $column
     * @param  mixed $input
     */
    public function like($column, $input);

    /**
     * Sql not like method.
     *
     * @param  mixed $column
     * @param  mixed $input
     */
    public function notLike($column, $input);

    /**
     * Sql in method.
     *
     * @param  mixed $column
     * @param  array  $input
     */
    public function in($column, array $input);

    /**
     * Sql not in method.
     *
     * @param  mixed $column
     * @param  array  $input
     */
    public function notIn($column, array $input);

    /**
     * Sql between method.
     *
     * @param  mixed $column
     * @param  array  $inputs
     */
    public function between($column, array $inputs);

    /**
     * Sql not between method.
     *
     * @param  mixed $column
     * @param  array  $inputs
     */
    public function notBetween($column, array $inputs);

    /**
     * Sql is null method.
     *
     * @param  mixed  $column
     */
    public function isNull($column);

    /**
     * Sql is not null method.
     *
     * @param  mixed  $column
     */
    public function isNotNull($column);

    /**
     * Prepare query method.
     */
    public function query();

    /**
     * Execute query method.
     */
    public function execute();

    /**
     * Set data method.
     *
     * @param mixed $input
     */
    public function setData($input);

    /**
     * Get data method.
     */
    public function getData();

    /**
     * Set sql method.
     *
     * @param mixed $clause
     */
    public function setSql($clause);

    /**
     * Get sql method.
     */
    public function getSql();

    /**
     * Reset method.
     */
    public function reset();

    /**
     * Filling unnamed parameters based on the values.
     *
     * @param array $values
     */
    public function unnamedParams(array $values);
}
