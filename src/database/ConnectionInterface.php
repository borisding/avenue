<?php
namespace Avenue\Database;

interface ConnectionInterface
{
    /**
     * Get PDO database connection object.
     */
    public function getDatabaseConnection();
    
    /**
     * Disconnect database connection by destroying PDO object.
     */
    public function disconnectDatabase();
    
    /**
     * Get database configuration.
     */
    public function getDatabaseConfig();
    
    /**
     * Get database username attribute.
     */
    public function getDatabaseUsername();
    
    /**
     * Get database password attribute.
     */
    public function getDatabasePassword();
    
    /**
     * Get database dsn attribute.
     */
    public function getDatabaseDsn();
    
    /**
     * Get database persist attribute.
     */
    public function getDatabasePersist();
    
    /**
     * Get database emulate attribute.
     */
    public function getDatabaseEmulate();
    
    /**
     * Get database table prefix.
     */
    public function getTablePrefix();
    
    /**
     * Get the current PDO driver to be used.
     */
    public function getCurrentPdoDriver();
    
    /**
     * Get all available PDO drivers.
     */
    public function getPdoDrivers();
}