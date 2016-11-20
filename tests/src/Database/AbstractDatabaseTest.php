<?php
namespace Avenue\Tests\Database;

use Avenue\App;
use Avenue\Database\Command;

abstract class AbstractDatabaseTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    protected $db;

    protected $table;

    protected $data = [
        1 => 'PHP',
        2 => 'JavaScript',
        3 => 'C/C++',
        4 => 'Java',
        5 => 'Go'
    ];

    public function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('[pdo_sqlite] is required for testing sqlite memory.');
        }

        $this->app = new App($this->config, uniqid(rand()));
        $this->db = new Command();

        $this->db->setTable('programming');
        $this->table = $this->db->getTable();
        $this->prepareMasterData();
    }
    
    protected function prepareMasterData()
    {
        $this->db->cmd($this->createTableSql())->run();

        foreach ($this->data as $id => $name) {
            $this->db->cmd($this->insertSql())->runWith([$id, $name]);
        }
    }

    protected function prepareSlaveData()
    {
        $this->db->cmd($this->createTableSql(), true)->run();

        foreach ($this->data as $id => $name) {
            $this->db->cmd($this->insertSql(), true)->runWith([$id, $name]);
        }
    }

    protected function createTableSql()
    {
        return "CREATE TABLE programming (
            id   INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL
        )";
    }

    protected function insertSql()
    {
        return sprintf('insert into %s values (?, ?)', $this->table);
    }

    protected function selectAllSql()
    {
        return sprintf('select * from %s', $this->table);
    }
}
