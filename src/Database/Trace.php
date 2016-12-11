<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Database\CommandTrait;
use Avenue\Database\QueryBuilderTrait;
use Avenue\Database\SqlDebuggerTrait;
use Avenue\Interfaces\Database\TraceInterface;

class Trace implements TraceInterface
{
    use CommandTrait;
    use QueryBuilderTrait;
    use SqlDebuggerTrait;

    /**
     * App class instance.
     *
     * @var \Avenue\App
     */
    protected $app;

    /**
     * Model's table name.
     *
     * @var mixed
     */
    protected $table;

    /**
     * Default primary key column.
     *
     * @var mixed
     */
    protected $pk = 'id';

    /**
     * Foreign key.
     *
     * @var mixed
     */
    protected $fk;

    /**
     * Connection class instance.
     *
     * @var \Avenue\Database\Connection
     */
    private $connection;

    /**
     * Prepared statement.
     *
     * @var mixed
     */
    private $statement;

    /**
     * SQL statement.
     *
     * @var string
     */
    private $sql;

    /**
     * SQL where keyword exist.
     *
     * @var boolean
     */
    private $whereExist = false;

    /**
     * SQL params data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Params for set/get magic methods
     *
     * @var array
     */
    private $params = [];

    /**
     * Supported fetch types.
     *
     * @var array
     */
    private $fetchTypes = [
        'both'  => PDO::FETCH_BOTH,
        'obj'   => PDO::FETCH_OBJ,
        'class' => PDO::FETCH_CLASS,
        'num'   => PDO::FETCH_NUM,
        'assoc' => PDO::FETCH_ASSOC
    ];

    /**
     * Fetch alias methods.
     *
     * @var array
     */
    private $fetchAlias = [
        'bothAll'   => 'both',
        'objAll'    => 'obj',
        'numAll'    => 'num',
        'assocAll'  => 'assoc',
        'bothOne'   => 'both',
        'objOne'    => 'obj',
        'numOne'    => 'num',
        'assocOne'  => 'assoc'
    ];

    /**
     * Command class constructor.
     * Instantiate connection class.
     */
    public function __construct()
    {
        if (!$this->app instanceof App) {
            $this->app = App::getInstance();
        }
        
        // create connection class instance if does not exist
        if (!$this->connection instanceof Connection) {
            $this->connection = new Connection($this->app);
        }
    }

    /**
     * Return the current connection class instance.
     *
     * @return \Avenue\Database\Connection
     */
    public function getConnectionInstance()
    {
        if (!$this->connection instanceof Connection) {
            throw new \InvalidArgumentException('Failed to get connection class instance.');
        }

        return $this->connection;
    }

    /**
     * Find record(s) with or without columns.
     * Default select all columns.
     *
     * @param  array $columns
     * @return $this
     */
    public function find(array $columns = [])
    {
        return $this
        ->select($columns)
        ->from($this->table);
    }

    /**
     * Find count number of records.
     * Default is count all.
     *
     * @param  string $columns
     * @return $this
     */
    public function findCount($columns = '*')
    {
        return $this
        ->selectCount($columns)
        ->from($this->table);
    }

    /**
     * Find distinct records.
     * Default is distinct all.
     *
     * @param  string $columns
     * @return $this
     */
    public function findDistinct($columns = '*')
    {
        return $this
        ->selectDistinct($columns)
        ->from($this->table);
    }

    /**
     * Find record(s) by ID provided.
     * Default select all columns.
     *
     * @param  mixed $id
     * @param  array $columns
     * @return $this
     */
    public function findById($id, array $columns = [])
    {
        return $this
        ->select($columns)
        ->from($this->table)
        ->where($this->pk, $id);
    }

    /**
     * Insert or update by saving.
     *
     * @param  mixed $id
     * @return boolean
     */
    public function save($id = null)
    {
        if (empty($id)) {
            return $this
            ->insert($this->table, $this->params)
            ->execute();
        } else {
            return $this
            ->update($this->table, $this->params)
            ->where($this->pk, $id)
            ->execute();
        }
    }

    /**
     * Delete particular record by its primary key value.
     *
     * @param  mixed $id
     * @return boolean
     */
    public function removeById($id)
    {
        return $this
        ->delete($this->table)
        ->where($this->pk, $id)
        ->execute();
    }

    /**
     * Delete ALL records from table.
     *
     * @return boolean
     */
    public function removeAll()
    {
        return $this
        ->delete($this->table)
        ->execute();
    }

    /**
     * One-to-one relationship method.
     * Targeted model class object need to be passed for mapping.
     *
     * @param  object  $targetObj
     * @return $this
     */
    public function hasOne($targetObj)
    {
        $tablePk = sprintf('%s.%s', $this->table, $this->pk);
        $targetFk = sprintf('%s.%s', $targetObj->table, $targetObj->fk);

        return $this
        ->innerJoin($targetObj->table, [$tablePk => $targetFk]);
    }

    /**
     * One-to-many relationship method.
     * Targeted model class object need to be passed for mapping.
     *
     * @param  object  $targetObj
     * @return $this
     */
    public function hasMany($targetObj)
    {
        $tablePk = sprintf('%s.%s', $this->table, $this->pk);
        $targetFk = sprintf('%s.%s', $targetObj->table, $targetObj->fk);

        return $this
        ->leftJoin($targetObj->table, [$tablePk => $targetFk]);
    }

    /**
     * Many-to-many relationship method.
     * Junction table, foreign keys (in associative array) and targeted model object to be provided.
     * Key will be mapping current model's table, and value will be mapping targeted model.
     *
     * @param  mixed  $junctionTable
     * @param  array   $junctionKeys
     * @param  object  $targetObj
     * @return $this
     */
    public function hasManyThrough($junctionTable, array $junctionKeys, $targetObj)
    {
        $currentPk = sprintf('%s.%s', $this->table, $this->pk);
        $targetPk = sprintf('%s.%s', $targetObj->table, $targetObj->pk);

        $currentJunctionFk = sprintf('%s.%s', $junctionTable, key($junctionKeys));
        $targetJunctionFk = sprintf('%s.%s', $junctionTable, current($junctionKeys));

        return $this
        ->leftJoin($junctionTable, [$currentPk => $currentJunctionFk])
        ->leftJoin($targetObj->table, [$targetJunctionFk => $targetPk]);
    }

    /**
     * Assign input to data list based on the provided input.
     *
     * @param  mixed $input
     */
    public function setData($input)
    {
        if (is_array($input)) {
            $this->data = array_merge($this->data, $input);
        } else {
            array_push($this->data, $input);
        }
    }

    /**
     * Return current stored data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Build sql statement with clause provided.
     *
     * @param  mixed $clause
     * @return string
     */
    public function setSql($clause)
    {
        return $this->sql .= $clause;
    }

    /**
     * Return current built sql and clear it.
     *
     * @return string
     */
    public function getSql()
    {
        $sql = $this->sql;
        $this->sql = '';
        $this->whereExist = false;

        return $sql;
    }

    /**
     * Return the filled unnamed parameters based on the values.
     *
     * @param  array  $values
     * @return string
     */
    public function unnamedParams(array $values)
    {
        return $this->app->fillRepeat('?', ', ', 0, count($values));
    }

    /**
     * Reset persisted sql statement and data.
     *
     * @return $this;
     */
    public function reset()
    {
        $this->sql = '';
        $this->data = [];
        $this->params = [];
        $this->whereExist = false;

        return $this;
    }

    /**
     * Set magic method.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Get magic method.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->app->arrGet($key, $this->params);
    }

    /**
     * Fetch alias method via magic call method.
     * If none is found, throw invalid method exception.
     *
     * eg:
     * for multiple records as object
     *
     * `$this->cmd('...')->objAll()` is same with `$this->cmd('...')->all('obj')`
     *
     * for single record as object
     * `$this->cmd('...')->objOne()` is same with `$this->cmd('...')->one('obj')`
     *
     * @param mixed $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, array $params = [])
    {
        if (!isset($this->fetchAlias[$method])) {
            throw new \PDOException('Calling invalid method ['. $method .']');
        }

        // for single record
        if (strtolower(substr($method, -3)) === 'one') {
            return $this->one($this->fetchAlias[$method]);
        }

        // for multiple records
        return $this->all($this->fetchAlias[$method]);
    }
}
