<?php
namespace Avenue\Tests\Database;

use Avenue\Tests\Database\AbstractDatabaseTest;

class QueryBuilderTest extends AbstractDatabaseTest
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

    public function testSelectAllQuery()
    {
        $result = $this->db->select()->from($this->table)->query()->all();
        $this->assertEquals(5, count($result));
    }

    public function testSelectOneQuery()
    {
        $result = $this->db->select()->from($this->table)->query()->one();
        $this->assertEquals(1, $result['id']);
    }

    public function testSelectRecordWhereQuery()
    {
        $result = $this->db->select()->from($this->table)->where('id', 1)->query()->all();
        $this->assertEquals(1, count($result));
        $this->assertEquals('PHP', $result[0]['name']);
    }

    public function testSelectWithColumnQuery()
    {
        $result = $this->db->select('name')->from($this->table)->query()->all();
        $this->assertEquals(5, count($result));
        $this->assertTrue(!isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['name']));
    }

    public function testSelectWithColumnsQuery()
    {
        $result = $this->db->select(['id', 'name'])->from($this->table)->query()->all();
        $this->assertEquals(5, count($result));
        $this->assertTrue(isset($result[0]['id']));
        $this->assertTrue(isset($result[0]['name']));
    }

    public function testSelectLimitQuery()
    {
        $result = $this->db->select()->from($this->table)->limit(2)->query()->all();
        $this->assertEquals(2, count($result));
    }

    public function testSelectLimitOffsetAsSecondParamsQuery()
    {
        $result = $this->db->select()->from($this->table)->limit(2, 2)->query()->all();
        $this->assertEquals(2, count($result));
        $this->assertEquals(3, $result[0]['id']);
    }

    public function testSelectLimitOffsetMethodQuery()
    {
        $result = $this->db->select()->from($this->table)->limit(2)->offset(2)->query()->all();
        $this->assertEquals(2, count($result));
        $this->assertEquals(3, $result[0]['id']);
    }

    public function testSelectOrderByColumnQuery()
    {
        $result = $this->db->select()->from($this->table)->orderBy('id DESC')->query()->all();
        $this->assertEquals(5, $result[0]['id']);
    }

    public function testSelectOrderByColumnsQuery()
    {
        $result = $this->db->select()->from($this->table)->orderBy(['id DESC', 'name ASC'])->query()->all();
        $this->assertEquals(5, $result[0]['id']);
    }

    public function testSelectUnionQuery()
    {
        $sql1 = $this->db->select('name')->from($this->table)->where('id', 1)->getSql();
        $sql2 = $this->db->select('name')->from($this->table)->where('id', 2)->orWhere('id', '>', 4)->getSql();
        $result = $this->db->union([$sql1, $sql2])->query()->all();

        $this->assertEquals(3, count($result));
    }

    public function testSelectAndOrGroupingQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where('id', 1)
        ->orWhere(function($query) {
            $query->where('id', 2)->andWhere('name', '=', 'JavaScript');
        })
        ->query()
        ->all();

        $this->assertEquals(2, count($result));
    }

    public function testSelectGroupByQuery()
    {
        $result = $this->db->select(['name', 'COUNT(id) as total'])
        ->from($this->table)
        ->groupBy('name')
        ->query()
        ->all();

        $this->assertEquals(1, $result[0]['total']);
    }

    public function testSelectGroupByHavingQuery()
    {
        $result = $this->db->select(['name', 'COUNT(id) as total'])
        ->from($this->table)
        ->groupBy('name')
        ->having('COUNT(id)', '>', 1)
        ->query()
        ->all();

        $this->assertEquals(0, count($result));
    }

    public function testSelectLikeQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->like('name', '%php%'))
        ->query()
        ->all();

        $this->assertEquals(1, count($result));
    }

    public function testSelectNotLikeQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->notLike('name', '%php%'))
        ->query()
        ->all();

        $this->assertEquals(4, count($result));
    }

    public function testSelectInQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->in('id', [1, 2, 3]))
        ->query()
        ->all();

        $this->assertEquals(3, count($result));
    }

    public function testSelectNotInQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->notIn('id', [1, 2, 3]))
        ->query()
        ->all();

        $this->assertEquals(2, count($result));
    }

    public function testSelectBetweenQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->between('id', [1, 3]))
        ->query()
        ->all();

        $this->assertEquals(3, count($result));
    }

    public function testSelectWhereIsNullQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->isNull('name'))
        ->query()
        ->all();

        $this->assertEquals(0, count($result));
    }

    public function testSelectWhereIsNotNullQuery()
    {
        $result = $this->db->select()
        ->from($this->table)
        ->where($this->db->isNotNull('name'))
        ->query()
        ->all();

        $this->assertEquals(5, count($result));
    }

    public function testSelectJoinQuery()
    {
        $result = $this->db->select(['t1.name', 't2.name'])
        ->from([$this->table => 't1'])
        ->join([$this->table => 't2'], ['t1.id' => 't2.id'])
        ->query()
        ->all();

        $this->assertEquals(5, count($result));
    }

    public function testSelectInnerJoinQuery()
    {
        $result = $this->db->select(['t1.name', 't2.name'])
        ->from([$this->table => 't1'])
        ->innerJoin([$this->table => 't2'], ['t1.id' => 't2.id'])
        ->where('t1.id', 1)
        ->query()
        ->all();

        $this->assertEquals(1, count($result));
    }

    public function testSelectLeftJoinQuery()
    {
        $result = $this->db->select(['t1.name', 't2.name'])
        ->from([$this->table => 't1'])
        ->leftJoin([$this->table => 't2'], ['t1.id' => 't2.id'])
        ->where('t1.id', 1)
        ->orWhere('t2.id', 2)
        ->query()
        ->all();

        $this->assertEquals(2, count($result));
    }

    // test with get sql since right outer join is currently not support by sqlite
    public function testSelectRightJoinQuery()
    {
        $sql = $this->db->select(['t1.name', 't2.name'])
        ->from([$this->table => 't1'])
        ->rightJoin([$this->table => 't2'], ['t1.id' => 't2.id'])
        ->where($this->db->in('t1.id', [1, 2, 3]))
        ->getSql();

        $expected = 'SELECT t1.name, t2.name FROM programming AS t1 RIGHT OUTER JOIN programming AS t2 ON t1.id = t2.id WHERE t1.id IN (?, ?, ?)';
        $this->assertEquals($expected, $sql);
    }

    // test with get sql since full outer join is currently not support by sqlite
    public function testSelectFullJoinQuery()
    {
        $sql = $this->db->select(['t1.name', 't2.name'])
        ->from([$this->table => 't1'])
        ->fullJoin([$this->table => 't2'], ['t1.id' => 't2.id'])
        ->where($this->db->in('t1.id', [1, 2, 3]))
        ->getSql();

        $expected = 'SELECT t1.name, t2.name FROM programming AS t1 FULL OUTER JOIN programming AS t2 ON t1.id = t2.id WHERE t1.id IN (?, ?, ?)';
        $this->assertEquals($expected, $sql);
    }

    public function testSelectCountAllQuery()
    {
        $total = $this->db->selectCount()->from($this->table)->query()->column();
        $this->assertEquals(5, $total);
    }

    public function testSelectCountIdAsTotalQuery()
    {
        $total = $this->db->selectCount(['id' => 'total'])->from($this->table)->query()->column();
        $this->assertEquals(5, $total);
    }

    public function testSelectDistinctColumnQuery()
    {
        $this->db->cmd(sprintf('insert into %s (name) values (?)', $this->table))->bind(1, 'PHP')->run();
        $result = $this->db->selectDistinct('name')->from($this->table)->query()->all();
        $this->assertEquals(5, count($result));
    }

    public function testSelectDistinctColumnsQuery()
    {
        $result = $this->db->selectDistinct(['id', 'name'])->from($this->table)->query()->all();
        $this->assertEquals(5, count($result));
    }

    public function testInsertQuery()
    {
        $this->db->insert($this->table, ['name' => 'Ruby'])->execute();
        $result = $this->db->select()->from($this->table)->query()->all();
        $this->assertEquals(6, count($result));
    }

    public function testDeleteAllQuery()
    {
        $this->db->delete($this->table)->execute();
        $result = $this->db->select()->from($this->table)->query()->all();
        $this->assertEquals(0, count($result));
    }

    public function testDeleteRecordQuery()
    {
        $this->db->delete($this->table)->where('id', 1)->execute();
        $result = $this->db->select()->from($this->table)->query()->all();
        $this->assertEquals(4, count($result));
    }

    public function testUpdateAllQuery()
    {
        $this->db->update($this->table, ['name' => 'PHP'])->execute();
        $result = $this->db->select()->from($this->table)->where('name', 'PHP')->query()->all();
        $this->assertEquals(5, count($result));
    }

    public function testUpdateRecordQuery()
    {
        $this->db->update($this->table, ['name' => 'PHP'])->where('id', 2)->execute();
        $result = $this->db->select()->from($this->table)->where('name', 'PHP')->query()->all();
        $this->assertEquals(2, count($result));
    }
}
