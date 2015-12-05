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
     * @see \Avenue\Database\StreetInterface::find()
     */
    public function find()
    {
        if (is_array($this->columns) && !empty($this->columns)) {
            $this->sql = sprintf('%s %s %s %s', 'SELECT', implode(', ', $this->columns), 'FROM', $this->table);
        } else {
            $this->sql = sprintf('%s %s', 'SELECT * FROM', $this->table);
        }
        
        return $this;
    }
    
    /**
     * Shortcut of finding all records.
     * 
     * @see \Avenue\Database\StreetInterface::findAll()
     */
    public function findAll()
    {
        return $this->find()->getAll();
    }
    
    /**
     * Shortcut of finding one record.
     * 
     * @see \Avenue\Database\StreetInterface::findOne()
     */
    public function findOne()
    {
        return $this->find()->getOne();
    }
    
    /**
     * Where condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::where()
     */
    public function where($column, $value)
    {
        $this->sql .= sprintf(' %s %s %s', 'WHERE', $column, '= ?');
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Where column IN statement based on the list of values.
     * 
     * @see \Avenue\Database\StreetInterface::whereIn()
     */
    public function whereIn($column, array $values = [])
    {
        $placeholders = $this->app->arrFillJoin(', ', '?', 0, count($values));
        $this->sql .= sprintf(' %s %s %s %s', 'WHERE', $column, 'IN', '(' . $placeholders . ')');
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }
    
    /**
     * Where and condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::andWhere()
     */
    public function andWhere($column, $value)
    {
        $this->sql .= sprintf(' %s %s %s', 'AND', $column, '= ?');
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Where or condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::orWhere()
     */
    public function orWhere($column, $value)
    {
        $this->sql .= sprintf(' %s %s %s', 'OR', $column, '= ?');
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Order by the column(s) and sort type.
     * 
     * @see \Avenue\Database\StreetInterface::orderBy()
     */
    public function orderBy($sorting)
    {
        if (!empty($sorting)) {
            $this->sql .= sprintf(' %s %s', 'ORDER BY', $this->app->escape($sorting));
        }
        
        return $this;
    }
    
    /**
     * Limit row offset statement.
     * 
     * @see \Avenue\Database\StreetInterface::limit()
     */
    public function limit($row, $offset = 0)
    {
        $this->sql .= sprintf(' %s %d, %d', 'LIMIT', $offset, $row);
        return $this;
    }
    
    /**
     * Return all found records in associative array.
     * 
     * @see \Avenue\Database\StreetInterface::getAll()
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
     * @see \Avenue\Database\StreetInterface::getOne()
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
     * Remove record(s) based on the passed in ID(s)
     * 
     * @see \Avenue\Database\StreetInterface::remove()
     */
    public function remove($id)
    {
        try {
            $this->sql = sprintf('%s %s', 'DELETE FROM', $this->table);
            $this->getWhereIdCondition($id);
            
            $this
            ->cmd($this->sql)
            ->batch($this->values)
            ->run();
            
            $this->flush();
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Remove all records.
     * 
     * @see \Avenue\Database\StreetInterface::removeAll()
     */
    public function removeAll()
    {
        try {
            $this->sql = sprintf('%s %s', 'DELETE FROM', $this->table);
            $this->cmd($this->sql)->run();
            
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Save by either creating or updating record(s) based on the ID(s).
     * 
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
     * @see \Avenue\Database\StreetInterface::create()
     */
    public function create()
    {
        try {
            $this->columns = implode(', ', array_keys($this->data));
            $this->values = array_values($this->data);
            
            $placeholders = $this->app->arrFillJoin(', ', '?', 0, count($this->values));
            
            $this->sql = sprintf('%s %s %s ', 'INSERT INTO', $this->table, '(' . $this->columns . ')');
            $this->sql .= sprintf('%s %s', 'VALUES', '(' . $placeholders . ')');
            
            $this
            ->cmd($this->sql)
            ->batch($this->values)
            ->run();
            
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
     * @see \Avenue\Database\StreetInterface::update()
     */
    public function update($id)
    {
        try {
            $this->columns = implode(' = ?, ', array_keys($this->data)) . ' = ?';
            $this->values = array_values($this->data);
            
            $this->sql = sprintf('%s %s %s %s', 'UPDATE', $this->table, 'SET', $this->columns);
            $this->getWhereIdCondition($id);
            
            $this
            ->cmd($this->sql)
            ->batch($this->values)
            ->run();
            
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
            $this->whereIn($this->pk, $ids);
        } else {
            $this->where($this->pk, $id);
        }
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