<?php
namespace Avenue\Interfaces\Database;

interface ConnectionInterface
{
    /**
     * Get the database configuration.
     */
    public function getDatabaseConfig();

    /**
     * Get available PDO database drivers.
     */
    public function getDrivers();

    /**
     * Connect with master.
     */
    public function withMaster();

    /**
     * Connect with slave.
     */
    public function withSlave();
}