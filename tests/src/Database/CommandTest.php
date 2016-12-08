<?php
namespace Avenue\Tests\Database;

use Avenue\Database\Connection;
use Avenue\Tests\Database\AbstractDatabaseTest;
use Avenue\Tests\Mocks\Programming;
use Avenue\Tests\Reflection;
use stdClass;

class CommandTest extends AbstractDatabaseTest
{
    protected $config = [
        'timezone' => 'UTC',
        'environment' => 'development',
        'database' => [
            'development' => [
                'master' => [
                    'dsn' => 'sqlite::memory:'
                ],
                'slave' => [
                    'dsn' => 'sqlite::memory:'
                ]
            ]
        ]
    ];

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetConnectionInstanceInvalidArgumentException()
    {
        Reflection::setPropertyValue($this->db, 'connection', null);
        $this->assertTrue($this->db->getConnectionInstance() instanceof Connection);
    }

    public function testGetConnectionInstance()
    {
        $this->assertTrue($this->db->getConnectionInstance() instanceof Connection);
    }

    public function testFetchAllMethod()
    {
        $result = $this->db->cmd($this->selectAllSql())->all();
        $this->assertEquals(count($result), 5);
    }

    public function testFetchOneMethod()
    {
        $record = $this->db->cmd($this->selectAllSql())->one();
        $this->assertTrue(array_key_exists('id', $record));
    }

    public function testDefaultFetchMasterConnection()
    {
        $record1 = $this->db->cmd($this->selectAllSql())->one();
        $record2 = $this->db->cmd($this->selectAllSql(), false)->one();

        $this->assertEquals($record1, $record2);
    }

    public function testCmdMasterMethod()
    {
        $record1 = $this->db->cmdMaster($this->selectAllSql())->one();
        $record2 = $this->db->cmd($this->selectAllSql(), false)->one();
        $this->assertEquals($record1, $record2);
    }

    public function testCmdSlaveMethod()
    {
        $this->prepareSlaveData();
        $record1 = $this->db->cmdSlave($this->selectAllSql())->one();
        $record2 = $this->db->cmd($this->selectAllSql(), true)->one();
        $this->assertEquals($record1, $record2);
    }

    public function testFetchAllMethodReturnDefaultAssociativeData()
    {
        $result = $this->db->cmd($this->selectAllSql())->all();
        $this->assertTrue($this->app->arrIsAssoc($result[0]));
    }

    public function testFetchAllMethodReturnAssociativeData()
    {
        $result = $this->db->cmd($this->selectAllSql())->all('assoc');
        $this->assertTrue($this->app->arrIsAssoc($result[0]));
    }

    public function testFetchAllMethodReturnBothData()
    {
        $result = $this->db->cmd($this->selectAllSql())->all('both');
        $singleRecord = $result[0];

        $this->assertEquals($singleRecord['id'], $singleRecord[0]);
    }

    public function testFetchAllMethodReturnObjectData()
    {
        $result = $this->db->cmd($this->selectAllSql())->all('obj');
        $this->assertTrue($result[0] instanceof stdClass);
    }

    public function testFetchAllMethodReturnNumData()
    {
        $result = $this->db->cmd($this->selectAllSql())->all('num');
        $this->assertTrue($this->app->arrIsIndex($result[0]));
    }

    public function testFetchBothAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->bothAll();
        $result2 = $this->db->cmd($this->selectAllSql())->all('both');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchObjAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->objAll();
        $result2 = $this->db->cmd($this->selectAllSql())->all('obj');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchNumAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->numAll();
        $result2 = $this->db->cmd($this->selectAllSql())->all('num');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAssocAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->assocAll();
        $result2 = $this->db->cmd($this->selectAllSql())->all('assoc');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchBothOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->bothOne();
        $result2 = $this->db->cmd($this->selectAllSql())->one('both');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchObjOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->objOne();
        $result2 = $this->db->cmd($this->selectAllSql())->one('obj');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchNumOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->numOne();
        $result2 = $this->db->cmd($this->selectAllSql())->one('num');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAssocOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->assocOne();
        $result2 = $this->db->cmd($this->selectAllSql())->one('assoc');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchColumnMethod()
    {
        $sql = sprintf('select count(id) as total from %s', $this->table);
        $total = $this->db->cmd($sql)->column();
        $this->assertEquals($total, count(array_values($this->data)));
    }

    public function testFetchClassAllMethod()
    {
        $result = $this->db->cmd($this->selectAllSql())->classAll(Programming::class);
        $this->assertEquals($result[0]->getId(), 1);
    }

    public function testFetchClassOneMethod()
    {
        $record = $this->db->cmd($this->selectAllSql())->classOne(Programming::class);
        $this->assertEquals($record->getId(), 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFetchClassAllMethodException()
    {
        $class = '\App\Models\Mocks\UnknownClass';
        $result = $this->db->cmd($this->selectAllSql())->classAll($class);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFetchClassOneMethodException()
    {
        $class = '\App\Models\Mocks\UnknownClass';
        $result = $this->db->cmd($this->selectAllSql())->classOne($class);
    }

    public function testDebugSqlWithNamedParameters()
    {
        $sql = 'select * from programming where name = :name or id = :id';
        $params = [':name' => 'php', ':id' => 1];
        $rawSql = $this->db->debug($sql, $params);

        $this->assertEquals("[SQL] select * from programming where name = 'php' or id = 1", $rawSql);
    }

    public function testDebugSqlWithNamedSqlUnnamedParameters()
    {
        $sql = 'select * from programming where name = ? or id = ?';
        $params = ['php', 1];
        $rawSql = $this->db->debug($sql, $params);

        $this->assertEquals("[SQL] select * from programming where name = 'php' or id = 1", $rawSql);
    }
}
