<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Interfaces\Database\ConnectionInterface;

class Connection implements ConnectionInterface
{
    /**
     * App class instance.
     *
     * @var object
     */
    protected $app;

    /**
     * Master connection.
     *
     * @var mixed
     */
    protected $master;

    /**
     * Slave connection.
     * @var mixed
     */
    protected $slave;

    /**
     * Master config.
     *
     * @var array
     */
    protected $masterConfig = [];

    /**
     * Slave config.
     *
     * @var array
     */
    protected $slaveConfig = [];

    /**
     * Database config.
     *
     * @var array
     */
    protected $databaseConfig = [];

    /**
     * Default config for master/slave.
     *
     * @var array
     */
    protected $defaultConfig = [
        'dsn' => '',
        'username' => '',
        'password' => '',
        'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    ];

    /**
     * Connection class constructor.
     * Define respective database config.
     *
     * @param App $app
     * @throws \InvalidArgumentException
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->databaseConfig = $this->getDatabaseConfig();

        if (empty($this->databaseConfig)) {
            throw new \InvalidArgumentException(sprintf(
                'Database is not configured for [%s] environment!',
                $this->app->getEnvironment()
            ));
        }

        // assign respective configurations for master/slave
        $this->masterConfig = $this->app->arrGet('master', $this->databaseConfig, []);
        $this->slaveConfig = $this->app->arrGet('slave', $this->databaseConfig, []);
    }

    /**
     * Retrieve database configuration based on environment.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getDatabaseConfig()
     */
    public function getDatabaseConfig()
    {
        return $this->app->arrGet(
            $this->app->getEnvironment(),
            $this->app->getConfig('database'),
            []
        );
    }

    /**
     * Get the PDO available database driver.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getDrivers()
     */
    public function getDrivers()
    {
        return PDO::getAvailableDrivers();
    }

    /**
     * Connect master database via PDO connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::withMaster()
     */
    public function withMaster()
    {
        // avoid reconnect if already connected for same master
        if ($this->master instanceof PDO) {
            return $this->master;
        }

        $this->master = $this->connectPdo($this->masterConfig);
        return $this->master;
    }

    /**
     * Connect with slave database via PDO connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::withSlave()
     */
    public function withSlave()
    {
        // if slave is not configured, divert to master instead
        if (empty($this->slaveConfig)) {
            $this->slave = $this->withMaster();
        // for multiple slaves, pick random slave
        } elseif ($this->app->arrIsIndex($this->slaveConfig)) {
            $this->slave = $this->connectPdo($this->slaveConfig[array_rand($this->slaveConfig)]);
        // for single slave, only reconnect if not established previously
        } elseif (!$this->slave instanceof PDO) {
            $this->slave = $this->connectPdo($this->slaveConfig);
        }

        return $this->slave;
    }

    /**
     * Establish database connection via PDO with config.
     *
     * @param array $config
     * @throws \RuntimeException
     * @return \PDO
     */
    protected function connectPdo(array $config)
    {
        try {
            extract(array_merge($this->defaultConfig, $config));

            // replace with user's driver option, if any
            $options = array_replace(
                $this->defaultConfig['options'],
                $this->app->arrGet('options', $config, [])
            );

            return new PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            $this->app->response->withStatus(500);
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Destrcutor for disconnecting database.
     */
    public function __destruct()
    {
        $this->master = null;
        $this->slave = null;
    }
}