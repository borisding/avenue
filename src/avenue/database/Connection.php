<?php
namespace Avenue\Database;

use \PDO;
use Avenue\App;

class Connection
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * Database config.
     * 
     * @var array
     */
    protected $dbConfig = [];
    
    /**
     * Database connection instance.
     * 
     * @var mixed
     */
    protected static $dbConn;
    
    /**
     * Connection class constructor.
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        $this->dbConfig = $this->getDatabaseConfig();
        
        if (empty($this->dbConfig)) {
            throw new \InvalidArgumentException('Database configuration is empty!');
        }
    }
    
    /**
     * Get PDO database connection object.
     * 
     * @return mixed
     */
    public function getDatabaseConnection()
    {
        if (empty(static::$dbConn)) {
            static::$dbConn = $this->establishConnection();
        }
        
        return static::$dbConn;
    }
    
    /**
     * Establish the database connection.
     * 
     * @throws \RuntimeException
     */
    protected function establishConnection()
    {
        try {
            $dsn = $this->getDatabaseDsn();
            $username = $this->getDatabaseUsername();
            $password = $this->getDatabasePassword();
            $persist = $this->getDatabasePersist();
            $emulate = $this->getDatabaseEmulate();
            
            $options = [
                PDO::ATTR_PERSISTENT => $persist,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
            
            if ($emulate && constant('PDO::ATTR_EMULATE_PREPARES')) {
                $options[PDO::ATTR_EMULATE_PREPARES] = $emulate;
            }
            
            return new PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Disconnect database connection by destroying PDO object.
     * 
     * @return NULL
     */
    public function disconnectDatabase()
    {
        return static::$dbConn = null;
    }
    
    /**
     * Get database configuration.
     */
    public function getDatabaseConfig()
    {
        $environment = $this->app->getConfig('environment');
        $database = $this->app->getConfig('database');
    
        return $this->app->arrGet($environment, $database, []);
    }
    
    /**
     * Get database username attribute.
     */
    public function getDatabaseUsername()
    {
        return $this->app->arrGet('username', $this->dbConfig);
    }
    
    /**
     * Get database password attribute.
     */
    public function getDatabasePassword()
    {
        return $this->app->arrGet('password', $this->dbConfig);
    }
    
    /**
     * Get database dsn attribute.
     */
    public function getDatabaseDsn()
    {
        return $this->app->arrGet('dsn', $this->dbConfig);
    }
    
    /**
     * Get database persist attribute.
     */
    public function getDatabasePersist()
    {
        return $this->app->arrGet('persist', $this->dbConfig);
    }
    
    /**
     * Get database emulate attribute.
     */
    public function getDatabaseEmulate()
    {
        return $this->app->arrGet('emulate', $this->dbConfig);
    }
    
    /**
     * Get database table prefix.
     */
    public function getTablePrefix()
    {
        return $this->app->arrGet('tablePrefix', $this->dbConfig);
    }
    
    /**
     * Get all available PDO drivers.
     */
    public function getPdoDrivers()
    {
        return PDO::getAvailableDrivers();
    }
}