<?php
namespace Avenue\Tests\Database;

use Avenue\App;
use Avenue\Database\Command;
use Avenue\Database\Connection;
use Avenue\Tests\Database\AbstractDatabaseTest;
use Avenue\Tests\Mocks\Programming;
use Avenue\Tests\Reflection;
use stdClass;

class CommandWrapperTest extends AbstractDatabaseTest
{
    protected $config = [
        'timezone' => 'UTC',
        'environment' => 'development',
        'database' => [
            'development' => [
                'master' => [
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

    public function testSelectAllWithOrderByClauseInUpperCase()
    {
        $result = $this->db->selectAll('ORDER BY id DESC');
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

    public function testSelectAllWithWhereConditionOrderByClause()
    {
        $result = $this->db->selectAll('id > ? order by id desc', 3);
        $this->assertEquals('Go', $result[0]['name']);
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

    public function testSelectWithOrderByClauseInUpperCase()
    {
        $result = $this->db->select(['name'], 'ORDER BY id DESC');
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

    public function testSelectWithWhereConditionOrderByClause()
    {
        $result = $this->db->select(['name'], 'id > ? order by id desc', 3);
        $this->assertEquals('Go', $result[0]['name']);
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
        $this->db->update(['name' => 'Scala'], 'id = :id', [':id' => 1]);
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

    public function testNewRecordThroughUpsert()
    {
        $this->db->upsert(6, [
            'id' => 6,
            'name' => 'Elixir'
        ]);

        $result = $this->db->select(['name']);
        $this->assertEquals(6, count($result));
    }

    public function testUpdateRecordThroughUpsert()
    {
        $this->db->upsert(5, [
            'id' => 5,
            'name' => 'Elixir'
        ]);

        $result = $this->db->select(['name'], 'id = ?', 5);
        $this->assertEquals('Elixir', $result[0]['name']);
    }
}
