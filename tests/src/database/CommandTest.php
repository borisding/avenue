<?php
namespace Avenue\Tests\Database;

use Avenue\App;
use Avenue\Database\Command;
use Avenue\Tests\Database\AbstractDatabaseTest;
use Avenue\Tests\Reflection;
use stdClass;

require_once AVENUE_TESTS_DIR . '/src/mocks/Programming.php';

class CommandTest extends AbstractDatabaseTest
{
    private $db;

    private $table;

    private $data = [
        1 => 'PHP',
        2 => 'JavaScript',
        3 => 'C/C++',
        4 => 'Java',
        5 => 'Go'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->db = new Command($this->app);
        Reflection::setPropertyValue($this->db, 'table', 'programming');

        $this->table = Reflection::getPropertyValue($this->db, 'table');
        $this->prepareMasterData();
    }

    private function prepareMasterData()
    {
        $this->db
        ->cmd($this->createTableSql())
        ->run();

        foreach ($this->data as $id => $name) {
            $this->db
            ->cmd($this->insertSql())
            ->runWith([$id, $name]);
        }
    }

    private function prepareSlaveData()
    {
        $this->db
        ->cmd($this->createTableSql(), false)
        ->run();

        foreach ($this->data as $id => $name) {
            $this->db
            ->cmd($this->insertSql(), false)
            ->runWith([$id, $name]);
        }
    }

    private function createTableSql()
    {
        return "CREATE TABLE programming (
            id   INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL
        )";
    }

    private function insertSql()
    {
        return sprintf('insert into %s values (?, ?)', $this->table);
    }

    private function selectAllSql()
    {
        return sprintf('select * from %s', $this->table);
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
        $record2 = $this->db->cmd($this->selectAllSql(), true)->fetchOne();

        $this->assertEquals($record1, $record2);
    }

    public function testCmdMasterMethod()
    {
        $record1 = $this->db->cmdMaster($this->selectAllSql())->fetchOne();
        $record2 = $this->db->cmd($this->selectAllSql(), true)->fetchOne();
        $this->assertEquals($record1, $record2);
    }

    public function testCmdSlaveMethod()
    {
        $this->prepareSlaveData();
        $record1 = $this->db->cmdSlave($this->selectAllSql())->fetchOne();
        $record2 = $this->db->cmd($this->selectAllSql(), false)->fetchOne();
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
        $class = '\App\Models\Mocks\Programming';
        $result = $this->db->cmd($this->selectAllSql())->fetchClassAll($class);
        $this->assertEquals($result[0]->getId(), 1);
    }

    public function testFetchClassOneMethod()
    {
        $class = '\App\Models\Mocks\Programming';
        $record = $this->db->cmd($this->selectAllSql())->fetchClassOne($class);
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

    /**
     * Tests for CRUD wrappers.
     */
    public function testSelectAllWithoutParameters()
    {
        $result = $this->db->selectAll();
        $this->assertEquals(5, count($result));
    }

    public function testSelectAllWithSingleUnnamedParameter()
    {
        $result = $this->db->selectAll('id = ?', 1);
        $this->assertEquals(1, count($result));
    }

    public function testSelectAllWithSingleNamedParameter()
    {
        $result = $this->db->selectAll('id = :id', [':id' => 1]);
        $this->assertEquals(1, count($result));
    }

    public function testSelectAllWithMultipleUnnamedParameters()
    {
        $result = $this->db->selectAll('id = ? or id = ?', [1, 2]);
        $this->assertEquals(2, count($result));
    }

    public function testSelectAllWithMultipleNamedParameters()
    {
        $result = $this->db->selectAll('id = :id1 or id = :id2', [':id1' => 1, ':id2' => 2]);
        $this->assertEquals(2, count($result));
    }

    public function testSelectAllWithLimitClause()
    {
        $result = $this->db->selectAll('limit ?', 3);
        $this->assertEquals(3, count($result));
    }

    public function testSelectAllWithOrderByClause()
    {
        $result = $this->db->selectAll('order by id desc');
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectAllWithOrderByLimitClause()
    {
        $result = $this->db->selectAll('order by id desc limit ?', 2);
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectAllWithWhereConditionAndOrderByLimitClause()
    {
        $result = $this->db->selectAll('id > ? order by id desc limit ?', [3, 2]);
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectAllWithWhereConditionLimitClause()
    {
        $result = $this->db->selectAll('id > ? limit ?', [3, 2]);
        $this->assertEquals('Java', $result[0]['name']);
    }

    public function testSelectAllWithExtraSpacesOrderByLimitClause()
    {
        $result = $this->db->selectAll('     order   by id desc  limit   ?', 2);
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectAllWithType()
    {
        $result = $this->db->selectAll('id = ?', 1, 'obj');
        $this->assertTrue($result[0] instanceof stdClass);
    }

    public function testSelectAllSlaveWithoutParameters()
    {
        $this->prepareSlaveData();
        $result = $this->db->selectAllSlave();
        $this->assertEquals(5, count($result));
    }

    public function testSelectWithColumn()
    {
        $result = $this->db->select(['name']);
        $this->assertEquals(5, count($result));
    }

    public function testSelectColumnWithSingleUnnamedParameter()
    {
        $result = $this->db->select(['name'], 'id = ?', 1);
        $this->assertEquals(1, count($result));
    }

    public function testSelectColumnWithSingleNamedParameter()
    {
        $result = $this->db->select(['name'], 'id = :id', [':id' => 1]);
        $this->assertEquals(1, count($result));
    }

    public function testSelectColumnWithMultipleUnnamedParameters()
    {
        $result = $this->db->select(['name'], 'id = ? or id = ?', [1, 2]);
        $this->assertEquals(2, count($result));
    }

    public function testSelectColumnWithMultipleNamedParameters()
    {
        $result = $this->db->select(['name'], 'id = :id1 or id = :id2', [':id1' => 1, ':id2' => 2]);
        $this->assertEquals(2, count($result));
    }

    public function testSelectSlaveWithoutParameters()
    {
        $this->prepareSlaveData();
        $result = $this->db->selectSlave(['name']);
        $this->assertEquals(5, count($result));
    }

    public function testSelectWithLimitClause()
    {
        $result = $this->db->select(['name'], 'limit ?', 3);
        $this->assertEquals(3, count($result));
    }

    public function testSelectWithOrderByClause()
    {
        $result = $this->db->select(['name'], 'order by id desc');
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectWithOrderByLimitClause()
    {
        $result = $this->db->select(['name'], 'order by id desc limit ?', 2);
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectWithWhereConditionAndOrderByLimitClause()
    {
        $result = $this->db->select(['name'], 'id > ? order by id desc limit ?', [3, 2]);
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectWithWhereConditionLimitClause()
    {
        $result = $this->db->select(['name'], 'id > ? limit ?', [3, 2]);
        $this->assertEquals('Java', $result[0]['name']);
    }

    public function testSelectWithExtraSpacesOrderByLimitClause()
    {
        $result = $this->db->select(['name'], '     order   by id desc  limit   ?', 2);
        $this->assertEquals('Go', $result[0]['name']);
    }

    public function testSelectWithType()
    {
        $result = $this->db->select(['name'], 'id = ?', 1, 'obj');
        $this->assertTrue($result[0] instanceof stdClass);
    }

    public function testInsert()
    {
        $this->db->insert(['name' => 'elixir']);
        $result = $this->db->select(['name']);
        $this->assertEquals(6, count($result));
    }

    public function testDeleteAll()
    {
        $this->db->deleteAll();
        $result = $this->db->select(['name']);
        $this->assertEquals(0, count($result));
    }

    public function testDeleteSingleWithUnnamedParameter()
    {
        $this->db->delete('id = ?', 1);
        $result = $this->db->select(['name']);
        $this->assertEquals(4, count($result));
    }

    public function testDeleteSingleWithNamedParameter()
    {
        $this->db->delete('id = :id', [':id' => 1]);
        $result = $this->db->select(['name']);
        $this->assertEquals(4, count($result));
    }

    public function testDeleteMultipleWithUnnamedParameters()
    {
        $this->db->delete('id = ? or id = ?', [1, 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals(3, count($result));
    }

    public function testDeleteMultipleWithNamedParameters()
    {
        $this->db->delete('id = :id1 or id = :id2', [':id1' => 1, ':id2' => 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals(3, count($result));
    }

    public function testUpdateAll()
    {
        $this->db->updateAll(['name' => 'Scala']);
        $result = $this->db->cmd(sprintf('select * from %s where name = \'Scala\'', $this->table))->fetchAll();
        $this->assertEquals(5, count($result));
    }

    public function testUpdateSingleWithUnnamedParameter()
    {
        $this->db->update(['name' => 'Scala'], 'id = ?', 1);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
    }

    public function testUpdateSingleWithNamedParameter()
    {
        $this->db->update(['name' => 'Scala'], 'id = :id', 1);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
    }

    public function testUpdateMultipleWithUnnamedParameters()
    {
        $this->db->update(['name' => 'Scala'], 'id = ? or id = ?', [1, 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
        $this->assertEquals('Scala', $result[1]['name']);
    }

    public function testUpdateMultipleWithNamedParameters()
    {
        $this->db->update(['name' => 'Scala'], 'id = :id1 or id = :id2', [':id1' => 1, ':id2' => 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
        $this->assertEquals('Scala', $result[1]['name']);
    }

    public function testGetPlaceholders()
    {
        $placeholders = $this->db->getPlaceholders([1, 2, 3]);
        $this->assertEquals('?, ?, ?', $placeholders);
    }
}