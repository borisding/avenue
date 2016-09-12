<?php
namespace Avenue\Tests\Database;

use Avenue\App;
use Avenue\Database\Command;
use Avenue\Tests\Database\AbstractDatabaseTest;
use stdClass;

require_once AVENUE_TESTS_DIR . '/src/mocks/Programming.php';

class CommandTest extends AbstractDatabaseTest
{
    private $db;

    private $table = 'programming';

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

    public function testFetchAllBothAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchAllBoth();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('both');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAllObjAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchAllObj();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('obj');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAllNumAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchAllNum();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('num');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchAllAssocAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchAllAssoc();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchAll('assoc');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchOneBothAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchOneBoth();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('both');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchOneObjAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchOneObj();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('obj');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchOneNumAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchOneNum();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('num');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchOneAssocAliasMethod()
    {
        $result1 = $this->db->cmd($this->selectAllSql())->fetchOneAssoc();
        $result2 = $this->db->cmd($this->selectAllSql())->fetchOne('assoc');

        $this->assertEquals($result1, $result2);
    }

    public function testFetchColumnMethod()
    {
        $sql = sprintf('select count(id) as total from %s', $this->table);
        $total = $this->db->cmd($sql)->fetchColumn();
        $this->assertEquals($total, count(array_values($this->data)));
    }

    public function testFetchClassMethod()
    {
        $class = '\App\Models\Mocks\Programming';
        $result = $this->db->cmd($this->selectAllSql())->fetchClass($class);
        $this->assertEquals($result[0]->getId(), 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFetchClassMethodException()
    {
        $class = '\App\Models\Mocks\UnknownClass';
        $result = $this->db->cmd($this->selectAllSql())->fetchClass($class);
    }
}