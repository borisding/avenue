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
     * @var \Avenue\App
     */
    protected $app;

    /**
     * Master connection.
     *
     * @var \PDO
     */
    protected $master;

    /**
     * Slave connection.
     *
     * @var \PDO
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
     * Default config for master/slave.
     *
     * @var array
     */
    protected $config = [
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
     * @param array $databaseConfig
     * @throws \InvalidArgumentException
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        // get database config
        $databaseConfig = $this->getDatabaseConfig();

        // assign respective configurations for master/slave
        $this->masterConfig = $this->app->arrGet('master', $databaseConfig, []);
        $this->slaveConfig = $this->app->arrGet('slave', $databaseConfig, []);

        // can assign directly to master config when `master` option is not applicable
        // this is to treat it as default single database
        if (empty($this->masterConfig)) {
            $this->masterConfig = $databaseConfig;
        }

        unset($databaseConfig);
    }

    /**
     * Get the database configuration based on the environment's setting.
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function getDatabaseConfig()
    {
        $environment = $this->app->getEnvironment();
        $config = $this->app->getConfig('database');
        $config = $this->app->arrGet($environment, $config, []);

        if (empty($config)) {
            throw new \InvalidArgumentException(
                sprintf('Database is not configured for [%s] environment!', $environment)
            );
        }

        return $config;
    }

    /**
     * Get all PDO available database drivers.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getDrivers()
     */
    public function getAllDrivers()
    {
        return PDO::getAvailableDrivers();
    }

    /**
     * Get master PDO connection driver.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getMasterDriver()
     */
    public function getMasterDriver()
    {
        if (!$this->master instanceof PDO) {
            $this->master = $this->getMasterPdo();
        }

        return $this->master->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Get slave PDO connection driver.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getSlaveDriver()
     */
    public function getSlaveDriver()
    {
        if (!$this->slave instanceof PDO) {
            $this->slave = $this->getSlavePdo();
        }

        return $this->slave->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Return either master/slave PDO connection.
     *
     * @param string $slave
     */
    public function getPdo($slave = false)
    {
        return ($slave === true) ? $this->getSlavePdo() : $this->getMasterPdo();
    }

    /**
     * Connect master database via PDO connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getMasterPdo()
     */
    public function getMasterPdo()
    {
        // avoid reconnect if already connected
        if ($this->master instanceof PDO) {
            return $this->master;
        }

        return $this->master = $this->createPdo($this->masterConfig);
    }

    /**
     * Connect with slave database via PDO connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::getSlavePdo()
     */
    public function getSlavePdo()
    {
        // if slave is not configured, divert to master instead
        if (empty($this->slaveConfig)) {
            $this->slave = $this->getMasterPdo();
        // for multiple slaves, pick random slave
        } elseif ($this->app->arrIsIndex($this->slaveConfig)) {
            $this->slave = $this->createPdo($this->slaveConfig[array_rand($this->slaveConfig)]);
        // for single slave, only reconnect if not established previously
        } elseif (!$this->slave instanceof PDO) {
            $this->slave = $this->createPdo($this->slaveConfig);
        }

        return $this->slave;
    }

    /**
     * Establish database connection via PDO instance with config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::createPdo()
     */
    public function createPdo(array $config)
    {
        try {
            extract(array_merge($this->config, $config));

            // replace with user's driver option, if any
            $options = array_replace(
                $this->config['options'],
                $this->app->arrGet('options', $config, [])
            );

            return new PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Disconnect both master and slave connections.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::disconnect()
     */
    public function disconnect()
    {
        $this->disconnectMaster();
        $this->disconnectSlave();
    }

    /**
     * Disconnect master PDO connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::disconnectMaster()
     */
    public function disconnectMaster()
    {
        return $this->master = null;
    }

    /**
     * Disconnect slave PDO connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\ConnectionInterface::disconnectSlave()
     */
    public function disconnectSlave()
    {
        return $this->slave = null;
    }
}