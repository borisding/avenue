<?php
namespace Avenue\Interfaces\Database;

interface CommandInterface
{
    /**
     * Get the connection instance.
     */
    public function getConnectionInstance();

    /**
     * Set table with provided name.
     *
     * @param mixed $name
     */
    public function setTable($name);

    /**
     * Get the table name.
     */
    public function getTable();

    /**
     * Set primary key with provided column name.
     *
     * @param mixed $name
     */
    public function setPk($name);

    /**
     * Get the primary key column name.
     */
    public function getPk();

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
    public function fetchAll($type);

    /**
     * Fetch single record method based on type.
     *
     * @param mixed $type
     */
    public function fetchOne($type);

    /**
     * Fetch single record with class behavior.
     *
     * @param mixed $name
     * @param array $ctorArgs
     */
    public function fetchClassOne($name, array $ctorArgs);

    /**
     * Fetch multiple records with class behavior.
     *
     * @param mixed $name
     * @param array $ctorArgs
     */
    public function fetchClassAll($name, array $ctorArgs);

    /**
     * Fetch a column from the next row.
     *
     * @param integer $number
     */
    public function fetchColumn($number);

    /**
     * Fetch and get total number of rows.
     */
    public function fetchTotalRows();

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
    public function getAffectedRows();

    /**
     * Get inserted ID.
     */
    public function getInsertedId();

    /**
     * Debug and dump params for prepared statement.
     */
    public function ddp();

    /**
     * Handling default select all query from master.
     *
     * @param mixed $clause
     * @param mixed $params
     * @param mixed $type
     */
    public function selectAll($clause, $params, $type);

    /**
     * Handling default select all query from slave.
     *
     * @param mixed $clause
     * @param mixed $params
     * @param mixed $type
     */
    public function selectAllSlave($clause, $params, $type);

    /**
     * Handling default select column(s) query from master.
     *
     * @param array $columns
     * @param mixed $clause
     * @param mixed $params
     * @param mixed $type
     */
    public function select(array $columns, $clause, $params, $type);

    /**
     * Handling default select column(s) query from slave.
     *
     * @param array $columns
     * @param mixed $clause
     * @param mixed $params
     * @param mixed $type
     */
    public function selectSlave(array $columns, $clause, $params, $type);

    /**
     * Handling insert query method.
     *
     * @param array $params
     */
    public function insert(array $columns);

    /**
     * Handling delete all records query method.
     */
    public function deleteAll();

    /**
     * Handling delete query method.
     *
     * @param mixed $clause
     * @param mixed $params
     */
    public function delete($clause, $params);

    /**
     * Handling update all query method.
     *
     * @param array $columns
     */
    public function updateAll(array $columns);

    /**
     * Handling update query method.
     *
     * @param array $columns
     * @param mixed $clause
     * @param mixed $params
     */
    public function update(array $columns, $clause, $params);

    /**
     * Implement update/insert based on the existense of the record.
     *
     * @param mixed $id
     * @param array $columns
     */
    public function upsert($id, array $columns);

    /**
     * Filling placeholders based on the values.
     *
     * @param array $values
     */
    public function getPlaceholders(array $values);
}