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

    public function testSetPk()
    {
        $this->db->setPk('pk_col');
        $pk = Reflection::getPropertyValue($this->db, 'pk');
        $this->assertEquals('pk_col', $pk);
    }

    public function testGetPk()
    {
        $this->db->setPk('pk_col');
        $this->assertEquals('pk_col', $this->db->getPk());
    }

    public function testFetchAllMethod()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchAll();
        $this->assertEquals(count($result), 5);
    }

    public function testFetchOneMethod()
    {
        $record = $this->db->cmd($this->selectAllSql())->fetchOne();
        $this->assertTrue(array_key_exists('id', $record));
    }

    public function testDefaultFetchMasterConnection()
    {
        $record1 = $this->db->cmd($this->selectAllSql())->fetchOne();
        $record2 = $this->db->cmd($this->selectAllSql(), false)->fetchOne();

        $this->assertEquals($record1, $record2);
    }

    public function testCmdMasterMethod()
    {
        $record1 = $this->db->cmdMaster($this->selectAllSql())->fetchOne();
        $record2 = $this->db->cmd($this->selectAllSql(), false)->fetchOne();
        $this->assertEquals($record1, $record2);
    }

    public function testCmdSlaveMethod()
    {
        $this->prepareSlaveData();
        $record1 = $this->db->cmdSlave($this->selectAllSql())->fetchOne();
        $record2 = $this->db->cmd($this->selectAllSql(), true)->fetchOne();
        $this->assertEquals($record1, $record2);
    }

    public function testFetchAllMethodReturnDefaultAssociativeData()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchAll();
        $this->assertTrue($this->app->arrIsAssoc($result[0]));
    }

    public function testFetchAllMethodReturnAssociativeData()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchAll('assoc');
        $this->assertTrue($this->app->arrIsAssoc($result[0]));
    }

    public function testFetchAllMethodReturnBothData()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchAll('both');
        $singleRecord = $result[0];

        $this->assertEquals($singleRecord['id'], $singleRecord[0]);
    }

    public function testFetchAllMethodReturnObjectData()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchAll('obj');
        $this->assertTrue($result[0] instanceof stdClass);
    }

    public function testFetchAllMethodReturnNumData()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchAll('num');
        $this->assertTrue($this->app->arrIsIndex($result[0]));
    }

    public function testFetchBothAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchBothAll();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('both');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchObjAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchObjAll();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('obj');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchNumAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchNumAll();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('num');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAssocAllAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchAssocAll();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('assoc');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchBothOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchBothOne();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('both');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchObjOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchObjOne();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('obj');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchNumOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchNumOne();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('num');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAssocOneAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchAssocOne();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('assoc');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchColumnMethod()
    {
        $sql = sprintf('select count(id) as total from %s', $this->table);
        $total = $this->db->cmd($sql)->fetchColumn();
        $this->assertEquals($total, count(array_values($this->data)));
    }

    public function testFetchClassAllMethod()
    {
        $result = $this->db->cmd($this->selectAllSql())->fetchClassAll(Programming::class);
        $this->assertEquals($result[0]->getId(), 1);
    }

    public function testFetchClassOneMethod()
    {
        $record = $this->db->cmd($this->selectAllSql())->fetchClassOne(Programming::class);
        $this->assertEquals($record->getId(), 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFetchClassAllMethodException()
    {
        $class = '\App\Models\Mocks\UnknownClass';
        $result = $this->db->cmd($this->selectAllSql())->fetchClassAll($class);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFetchClassOneMethodException()
    {
        $class = '\App\Models\Mocks\UnknownClass';
        $result = $this->db->cmd($this->selectAllSql())->fetchClassOne($class);
    }

    public function testDebugSqlWithNamedPlacheholders()
    {
        $rawSql = $this->db
        ->cmd('select * from programming where name = :name or id = :id')
        ->debug([':name' => 'php', ':id' => 1]);

        $this->assertEquals("[SQL] select * from programming where name = 'php' or id = 1", $rawSql);
    }

    public function testDebugSqlWithUnnamedPlacheholders()
    {
        $rawSql = $this->db
        ->cmd('select * from programming where name = ? or id = ?')
        ->debug(['php', 1]);

        $this->assertEquals("[SQL] select * from programming where name = 'php' or id = 1", $rawSql);
    }

    public function testDebugSqlWithNullValue()
    {
        $rawSql = $this->db
        ->cmd('select * from programming where name = ? or id = ?')
        ->debug([NULL, 1]);

        $this->assertEquals("[SQL] select * from programming where name = NULL or id = 1", $rawSql);
    }

    public function testDebugSqlWithBooleanValue()
    {
        $rawSql = $this->db
        ->cmd('select * from programming where name = ? or id = ?')
        ->debug([TRUE, 1]);

        $this->assertEquals("[SQL] select * from programming where name = TRUE or id = 1", $rawSql);
    }
}
