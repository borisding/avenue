<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\ConnectionInterface;

class Connection implements ConnectionInterface
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
    private $dbConfig = [];
    
    /**
     * Database connection instance.
     * 
     * @var mixed
     */
    private static $dbConn;
    
    /**
     * Current PDO driver to be used.
     * 
     * @var mixed
     */
    private static $pdoDriver;
    
    /**
     * Connection class constructor.
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        
        if (empty($this->dbConfig)) {
            $this->dbConfig = $this->getDatabaseConfig();
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabaseConnection()
     */
    public function getDatabaseConnection()
    {
        if (empty(self::$dbConn)) {
            self::$dbConn = $this->establishConnection();
        }
        
        return self::$dbConn;
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
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::disconnectDatabase()
     */
    public function disconnectDatabase()
    {
        return self::$dbConn = null;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabaseConfig()
     */
    public function getDatabaseConfig()
    {
        $environment = $this->app->getConfig('environment');
        $database = $this->app->getConfig('database');
    
        return $this->app->arrGet($environment, $database, []);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabaseUsername()
     */
    public function getDatabaseUsername()
    {
        return $this->app->arrGet('username', $this->dbConfig);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabasePassword()
     */
    public function getDatabasePassword()
    {
        return $this->app->arrGet('password', $this->dbConfig);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabaseDsn()
     */
    public function getDatabaseDsn()
    {
        return $this->app->arrGet('dsn', $this->dbConfig);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabasePersist()
     */
    public function getDatabasePersist()
    {
        return $this->app->arrGet('persist', $this->dbConfig);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getDatabaseEmulate()
     */
    public function getDatabaseEmulate()
    {
        return $this->app->arrGet('emulate', $this->dbConfig);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getTablePrefix()
     */
    public function getTablePrefix()
    {
        return $this->app->arrGet('tablePrefix', $this->dbConfig);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getCurrentPdoDriver()
     */
    public function getCurrentPdoDriver()
    {
        if (empty(self::$dbConn)) {
            $this->getDatabaseConnection();
        }
        
        if (empty(self::$pdoDriver)) {
            return self::$dbConn->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
        
        return self::$pdoDriver;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\ConnectionInterface::getPdoDrivers()
     */
    public function getPdoDrivers()
    {
        return PDO::getAvailableDrivers();
    }
}