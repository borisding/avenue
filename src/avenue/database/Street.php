<?php
namespace Avenue\Database;

use Avenue\Database\PdoAdapter;
use Avenue\Database\StreetInterface;
use Avenue\Database\StreetRelationTrait;

class Street extends PdoAdapter implements StreetInterface
{
    use StreetRelationTrait;
    
    /**
     * Targeted table.
     * 
     * @var mixed
     */
    protected $table;
    
    /**
     * Table primary key.
     * 
     * @var mixed
     */
    protected $pk;
    
    /**
     * SQL statement.
     * 
     * @var mixed
     */
    private $sql;
    
    /**
     * Where condition.
     * 
     * @var mixed
     */
    private $where;
    
    /**
     * Order by.
     * 
     * @var mixed
     */
    private $orderBy;
    
    /**
     * List of table columns.
     * 
     * @var array
     */
    private $columns = [];
    
    /**
     * List of respective assigned values.
     * 
     * @var array
     */
    private $values = [];
    
    /**
     * List of data values.
     * 
     * @var array
     */
    private $data = [];
    
    /**
     * Street class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->defineTable()->definePrimaryKey();
    }
    
    /**
     * Select statement preparation.
     * 
     * @return \Avenue\Database\Street
     */
    public function find()
    {
        if (is_array($this->columns) && !empty($this->columns)) {
            $this->sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;
        } else {
            $this->sql = 'SELECT * FROM ' . $this->table;
        }
        
        return $this;
    }
    
    /**
     * Shortcut of finding all records.
     * 
     * @return mixed
     */
    public function findAll()
    {
        return $this->find()->getAll();
    }
    
    /**
     * Shortcut of find one record.
     * 
     * @return mixed
     */
    public function findOne()
    {
        return $this->find()->getOne();
    }
    
    /**
     * Where condition accepts column and its value.
     * 
     * @param mixed $column
     * @param mixed $value
     * @return \Avenue\Database\Street
     */
    public function where($column, $value)
    {
        $this->sql .= ' WHERE ' . $column . ' = ?';
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Where and condition accepts column and its value.
     * 
     * @param mixed $column
     * @param mixed $value
     * @return \Avenue\Database\Street
     */
    public function andWhere($column, $value)
    {
        $this->sql .= ' AND ' . $column . ' = ? ';
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Where or condition accepts column and its value.
     * 
     * @param mixed $column
     * @param mixed $value
     * @return \Avenue\Database\Street
     */
    public function orWhere($column, $value)
    {
        $this->sql .= ' OR ' . $column . ' = ? ';
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Order by the column(s) and sort type.
     * 
     * @param mixed $sorting
     * @return \Avenue\Database\Street
     */
    public function orderBy($sorting)
    {
        $this->sql .= ' ORDER BY ' . $sorting;
        return $this;
    }
    
    /**
     * Return all found records in associative array.
     * 
     * @return mixed
     */
    public function getAll()
    {
        try {
            $result = $this
            ->cmd($this->sql)
            ->batch($this->values)
            ->fetchAll();
            
            $this->flush();
            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Return one record in associative array.
     * 
     * @return mixed
     */
    public function getOne()
    {
        try {
            $result = $this
            ->cmd($this->sql)
            ->batch($this->values)
            ->fetchOne();
            
            $this->flush();
            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::remove()
     */
    public function remove($id)
    {
        try {
            $sql = 'DELETE FROM ' . $this->table;
            $sql .= ' WHERE ' . $this->pk;
            $sql .= $this->getWhereIdCondition($id);
            
            $this->cmd($sql)->run();
            
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::removeAll()
     */
    public function removeAll()
    {
        try {
            $sql = 'DELETE FROM ' . $this->table;
            $this->cmd($sql)->run();
    
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::save()
     */
    public function save($id = null)
    {
        if (!empty($id)) {
            return $this->update($id);
        } else {
            return $this->create();
        }
    }
    
    /**
     * Create new record into database.
     * Last inserted ID will be returned.
     * 
     * @throws \RuntimeException
     */
    public function create()
    {
        try {
            $columns = implode(', ', array_keys($this->data));
            $values = array_values($this->data);
            $placeholders = $this->app->arrFillJoin(', ', '?', 0, count($values));
            
            $sql = 'INSERT INTO ' . $this->table . ' (' . $columns . ') ';
            $sql .= 'VALUES (' . $placeholders . ')';
            
            $this->cmd($sql)->batch($values)->run();
            $this->flush();
            
            return $this->getInsertedId();
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Update record based on the passed in ID.
     * ID can be a list or a value.
     * 
     * @param mixed $id
     * @throws \RuntimeException
     */
    public function update($id)
    {
        try {
            $columns = implode(' = ?, ', array_keys($this->data)) . ' = ?';
            $values = array_values($this->data);
            
            $sql = 'UPDATE ' . $this->table . ' SET ';
            $sql .= $columns . ' WHERE ' . $this->pk;
            $sql .= $this->getWhereIdCondition($id);
            
            $this->cmd($sql)->batch($values)->run();
            $this->flush();
            
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * List of selected table columns.
     * 
     * @param array $columns
     * @return \Avenue\Database\Street
     */
    public function column(array $columns = [])
    {
        $this->columns = $columns;
        return $this;
    }
    
    /**
     * // TODO
     * Get the where condition based on the id type.
     *
     * @param mixed $id
     */
    private function getWhereIdCondition($id)
    {
        // if $id is passed as a list
        // then use IN for multiple records action
        // else, go to one-to-one action instead
    
        if (is_array($id)) {
            $ids = $id;
            $ids = $this->app->escape(implode(', ', $ids));
            $condition = ' IN (' . $ids . ')';
        } else {
            $condition = ' = ' . $this->app->escape($id);
        }
        
        return $condition;
    }
    
    /**
     * Define the table name based on the model class name.
     * Wrapped with the table prefix syntax.
     */
    private function defineTable()
    {
        // assume table name is based on the defined model class name
        // unless it is clearly defined in model class with $table property itself
        if (empty($this->table)) {
            $this->table = strtolower($this->getModelName());
        }
        
        $this->table = '{' . $this->app->escape($this->table) . '}';

        return $this;
    }

    /**
     * Define the primary key of the table.
     * If none is defined, use default 'id' instead.
     */
    private function definePrimaryKey()
    {
        if (!empty($this->pk)) {
            $this->pk = $this->app->escape($this->pk);
        } else {
            $this->pk = 'id';
        }

        return $this;
    }
    
    /**
     * Get the model class name without the namespace.
     */
    private function getModelName()
    {
        $model = $namespace = get_class($this);
        
        if (strpos($namespace, '\\') !== false) {
            $arrNamespace = explode('\\', $namespace);
            $model = array_pop($arrNamespace);
        }
        
        return $model;
    }
    
    /**
     * Clear the properties with empty array.
     */
    private function flush()
    {
        $this->sql = null;
        $this->columns = [];
        $this->values = [];
        $this->data = [];
    }
    
    /**
     * Magic set method.
     * 
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Magic get method.
     * Return data based on the key.
     * 
     * @param mixed $key
     */
    public function __get($key)
    {
        return $this->app->arrGet($key, $this->data);
    }
}