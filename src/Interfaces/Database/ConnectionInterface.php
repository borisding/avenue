<?php
namespace Avenue\Interfaces\Database;

interface ConnectionInterface
{
    /**
     * Get all available PDO database drivers.
     */
    public function getAllDrivers();

    /**
     * Get current master PDO connection driver.
     */
    public function getMasterDriver();

    /**
     * Get current slave PDO connection driver.
     */
    public function getSlaveDriver();

    /**
     * Get master PDO connection.
     */
    public function getMasterPdo();

    /**
     * Get slave PDO connection.
     */
    public function getSlavePdo();

    /**
     * Get PDO connection.
     */
    public function getPdo();

    /**
     * Create PDO class instance with provided configuration.
     *
     * @param array $config
     */
    public function createPdo(array $config);

    /**
     * Disconnect PDO connection.
     */
    public function disconnect();

    /**
     * Disconnect master PDO connection.
     */
    public function disconnectMaster();

    /**
     * Disconnect slave PDO connection.
     */
    public function disconnectSlave();
}
