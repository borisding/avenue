<?php
namespace Avenue\Tests\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Tests\Reflection;
use Avenue\Tests\Database\AbstractDatabaseTest;

class ConnectionTest extends AbstractDatabaseTest
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testDatabaseIsNotConfiguredForEnvironnment()
    {
        $config = $this->config;
        $config['database'] = [];

        $app = new App();
        Reflection::setPropertyValue($app, 'config', $config, true);
        $connection = new Connection($app);
    }

    public function testGetAllDrivers()
    {
        $this->assertTrue(is_array($this->connection->getAllDrivers()));
    }

    public function testGetMasterDriver()
    {
        $this->connection->getMasterPdo();
        $this->assertNotNull($this->connection->getMasterDriver());
    }

    public function testGetSlaveDriver()
    {
        $this->connection->getSlavePdo();
        $this->assertNotNull($this->connection->getSlaveDriver());
    }

    public function testGetMasterPdo()
    {
        $master = $this->connection->getMasterPdo();
        $this->assertTrue($master instanceof PDO);
    }

    public function testGetSlavePdo()
    {
        $slave = $this->connection->getSlavePdo();
        $this->assertTrue($slave instanceof PDO);
    }

    public function testDivertSlaveToMasterWhenNotConfigured()
    {
        $config = $this->config;
        $config['database']['development']['slave'] = null;
        $app = new App();
        Reflection::setPropertyValue($app, 'config', $config, true);

        $connection = new Connection($app);
        $master = $connection->getMasterPdo();
        $slave = $connection->getSlavePdo();

        $this->assertEquals($slave, $master);
    }

    public function testConnectPdo()
    {
        $config = $this->config['database']['development']['master'];
        $pdo = $this->connection->connectPdo($config);
        $this->assertTrue($pdo instanceof PDO);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConnectPdoRuntimeException()
    {
        $config = $this->config['database']['development']['master'] = [];
        $pdo = $this->connection->connectPdo($config);
    }

    public function testDisconnect()
    {
        $this->assertAttributeEmpty('master', $this->connection);
        $this->assertAttributeEmpty('slave', $this->connection);
    }

    public function testDisconnectMaster()
    {
        $this->assertAttributeEmpty('master', $this->connection);
    }

    public function testDisconnectSlave()
    {
        $this->assertAttributeEmpty('slave', $this->connection);
    }
}