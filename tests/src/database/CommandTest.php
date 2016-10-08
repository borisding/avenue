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
        Reflection::setPropertyValue($this->db, 'pk', 'id');

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

    public function testSelectAllWithoutParameters()
    {
        $result = $this->db->selectAll();
        $this->assertEquals(5, count($result));
    }

    public function testSelectAllWithId()
    {
        $result = $this->db->selectAll(1);
        $this->assertEquals(1, count($result));
    }

    public function testSelectAllWithIds()
    {
        $result = $this->db->selectAll([1, 2, 3]);
        $this->assertEquals(3, count($result));
    }

    public function testSelectAllWithWithClause()
    {
        $result = $this->db->selectAll(1, ['or id = ?' => 2]);
        $this->assertEquals(2, count($result));
    }

    public function testSelectAllWithClauseMultipleValues()
    {
        $result = $this->db->selectAll(1, ['or id = ? or id = ?' => [2, 3]]);
        $this->assertEquals(3, count($result));
    }

    public function testSelectAllWithClauseAndType()
    {
        $result = $this->db->selectAll(1, ['or id = ?' => 2], 'obj');
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

    public function testSelectColumnWithId()
    {
        $result = $this->db->select(['name'], 1);
        $this->assertEquals(1, count($result));
    }

    public function testSelectColumnWithIds()
    {
        $result = $this->db->select(['name'], [1, 2, 3]);
        $this->assertEquals(3, count($result));
    }

    public function testSelectColumnWithClause()
    {
        $result = $this->db->select(['name'], 1, ['or id = ?' => 2]);
        $this->assertEquals(2, count($result));
    }

    public function testSelectColumnWithClauseMultipleValues()
    {
        $result = $this->db->select(['name'], 1, ['or id = ? or id = ?' => [2, 3]]);
        $this->assertEquals(3, count($result));
    }

    public function testSelectWithClauseAndType()
    {
        $result = $this->db->select(['name'], 1, ['or id = ?' => 2], 'obj');
        $this->assertTrue($result[0] instanceof stdClass);
    }

    public function testSelectSlaveCloumn()
    {
        $this->prepareSlaveData();
        $result = $this->db->select(['name']);
        $this->assertEquals(5, count($result));
    }

    public function testInsert()
    {
        $this->db->insert(['name' => 'elixir']);
        $result = $this->db->select(['name']);
        $this->assertEquals(6, count($result));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDeleteInvalidIdException()
    {
        $this->db->delete(null);
    }

    public function testDeleteSingle()
    {
        $this->db->delete(1);
        $result = $this->db->select(['name']);
        $this->assertEquals(4, count($result));
    }

    public function testDeleteMultiple()
    {
        $this->db->delete([1, 2, 3]);
        $result = $this->db->select(['name']);
        $this->assertEquals(2, count($result));
    }

    public function testDeleteMultipleWithClauseSingleValue()
    {
        $this->db->delete(1, ['or id = ?' => 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals(3, count($result));
    }

    public function testDeleteMultipleWithClauseMultipleValues()
    {
        $this->db->delete(1, ['or id = ? or id = ?' => [2, 3]]);
        $result = $this->db->select(['name']);
        $this->assertEquals(2, count($result));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUpdateInvalidParamsException()
    {
        $this->db->update([], 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUpdateInvalidIdException()
    {
        $this->db->update([[
            'name' => 'Scala'
        ]], null);
    }

    public function testUpdateSingle()
    {
        $this->db->update(['name' => 'Scala'], 1);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
    }

    public function testUpdateMultiple()
    {
        $this->db->update(['name' => 'Scala'], [1, 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
        $this->assertEquals('Scala', $result[1]['name']);
    }

    public function testUpdateWithClauseSingleValue()
    {
        $this->db->update(['name' => 'Scala'], 1, ['or id = ?' => 2]);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
        $this->assertEquals('Scala', $result[1]['name']);
    }

    public function testUpdateWithClauseMultipleValues()
    {
        $this->db->update(['name' => 'Scala'], 1, ['or id = ? or id = ?' => [2, 3]]);
        $result = $this->db->select(['name']);
        $this->assertEquals('Scala', $result[0]['name']);
        $this->assertEquals('Scala', $result[1]['name']);
        $this->assertEquals('Scala', $result[2]['name']);
    }
}