<?php
namespace Avenue\Interfaces\Database;

interface CommandInterface
{
    /**
     * Method for prepared statement.
     * Allowed to decide for master or slave.
     *
     * @param mixed $sql
     * @param mixed $master
     */
    public function cmd($sql, $master);

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
     * Define table for targeted model class.
     */
    public function getTableName();

    /**
     * Debug and dump params for prepared statement.
     */
    public function ddp();
}