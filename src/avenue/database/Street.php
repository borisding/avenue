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
     * Table foreign key.
     * 
     * @var mixed
     */
    protected $fk;
    
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
     * Columns are optional.
     * 
     * @see \Avenue\Database\StreetInterface::find()
     */
    public function find(array $columns = [])
    {
        $this->columns = $columns;
        
        if (is_array($this->columns) && !empty($this->columns)) {
            $this->sql = sprintf('SELECT %s FROM %s', implode(', ', $this->columns), $this->table);
        } else {
            $this->sql = sprintf('SELECT * FROM %s', $this->table);
        }
        
        return $this;
    }
    
    /**
     * Shortcut of finding all records.
     * Columns are optional.
     * 
     * @see \Avenue\Database\StreetInterface::findAll()
     */
    public function findAll(array $columns = [])
    {
        return $this->find($columns)->getAll();
    }
    
    /**
     * Shortcut of finding one record.
     * Columns are optional.
     * 
     * @see \Avenue\Database\StreetInterface::findOne()
     */
    public function findOne(array $columns = [])
    {
        return $this->find($columns)->getOne();
    }
    
    /**
     * Get all found record(s) via union by passing respective model objects
     * 
     * @see \Avenue\Database\StreetInterface::findUnion()
     */
    public function findUnion(array $objects)
    {
        $queries = [];
        $values = [];
        $i = 0;
        
        // iterate each model objects
        // store respective sql and values for placeholders, if any
        while ($i < count($objects)) {
            $that = $objects[$i];
            array_push($queries, $that->sql);
            $values = array_merge($values, $that->values);
            $i++;
        }
        
        // compute populated queries and values
        // so that can be invoked via prepared statement from the model that calls it
        $this->sql = '(' . implode(') UNION (', $queries) . ')';
        $this->values = $values;
        
        unset($that, $queries, $values);
        return $this;
    }
    
    /**
     * Where condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::where()
     */
    public function where($column, $value)
    {
        if (is_array($value)) {
            $values = $value;
            $this->whereIn($column, $values);
        } else {
            $this->sql .= sprintf(' WHERE %s %s', $column, '= ?');
            array_push($this->values, $value);
        }
        
        return $this;
    }
    
    /**
     * Where and condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::andWhere()
     */
    public function andWhere($column, $value)
    {
        $this->sql .= sprintf(' %s ', 'AND');
        
        if (is_array($value)) {
            $values = $value;
            $this->sql .= $this->getIn($column, $values);
        } else {
            $this->sql .= sprintf('%s %s', $column, '= ?');
            array_push($this->values, $value);
        }
        
        return $this;
    }
    
    /**
     * Where or condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::orWhere()
     */
    public function orWhere($column, $value)
    {
        $this->sql .= sprintf(' %s ', 'OR');
        
        if (is_array($value)) {
            $values = $value;
            $this->sql .= $this->getIn($column, $values);
        } else {
            $this->sql .= sprintf('%s %s', $column, '= ?');
            array_push($this->values, $value);
        }
        
        return $this;
    }
    
    /**
     * Where in statement for multiple values.
     * 
     * @see \Avenue\Database\StreetInterface::whereIn()
     */
    public function whereIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $this->sql .= sprintf(' WHERE %s IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }
    
    /**
     * Where not in statement for multiple values.
     * 
     * @see \Avenue\Database\StreetInterface::whereNotIn()
     */
    public function whereNotIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $this->sql .= sprintf(' WHERE %s NOT IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }
    
    /**
     * Get in statement.
     * 
     * @param mixed $column
     * @param array $values
     */
    protected function getIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $sql = sprintf(' %s IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
        
        return $sql;
    }
    
    /**
     * Get not in statement.
     * 
     * @param mixed $column
     * @param array $values
     */
    protected function getNotIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $sql = sprintf(' %s NOT IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
    
        return $sql;
    }
    
    /**
     * Group by column statement.
     * 
     * @see \Avenue\Database\StreetInterface::groupBy()
     */
    public function groupBy(array $columns)
    {
        $columns = implode(', ', $columns);
        
        if (!empty($columns)) {
            $this->sql .= sprintf(' %s %s', 'GROUP BY', $this->app->escape($columns));
        }
        
        return $this;
    }
    
    /**
     * Order by the column(s) and sort type.
     * 
     * @see \Avenue\Database\StreetInterface::orderBy()
     */
    public function orderBy(array $sorting)
    {
        $sorting = implode(', ', $sorting);
        
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
            $this->sql = sprintf('DELETE FROM %s', $this->table);
            $this->where($this->pk, $id);
            
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
            $this->sql = sprintf('DELETE FROM %s', $this->table);
            $this->cmd($this->sql)->run();
            
            $this->flush();
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
            $this->sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, $this->columns, $placeholders);
            
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
            
            $this->sql = sprintf('UPDATE %s SET %s', $this->table, $this->columns);
            $this->where($this->pk, $id);
            
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
     * Inner join statement.
     * 
     * @see \Avenue\Database\StreetInterface::join()
     */
    public function join($model, $on)
    {
        // if model passed in as model class object
        // try to get with model table property
        if (is_object($model)) {
            $table = $model->table;
        } else {
            $table = $model;
        }
        
        $this->sql .= sprintf(' INNER JOIN %s ON %s', $table, $on);
        unset($table);
        
        return $this;
    }
    
    /**
     * Define the table name based on the model class name.
     * Wrapped with the table prefix syntax.
     */
    protected function defineTable()
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
    protected function definePrimaryKey()
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
    protected function getModelName()
    {
        $model = $namespace = get_class($this);
        
        if (strpos($namespace, '\\') !== false) {
            $arrNamespace = explode('\\', $namespace);
            $model = array_pop($arrNamespace);
        }
        
        return $model;
    }
    
    /**
     * Get the targeted model class foreign key.
     * 
     * @param object $model
     * @throws \InvalidArgumentException
     */
    protected function getModelFk($model)
    {
        if (!is_object($model)) {
            throw new \InvalidArgumentException('Model is not an object.');
        }
        
        if (empty($model->fk)) {
            $model->fk = $this->table . '_id';
        }
        
        return $model->fk;
    }
    
    /**
     * Get the placeholders based on the values.
     * 
     * @param array $values
     */
    protected function getPlaceholders(array $values = [])
    {
        return $this->app->arrFillJoin(', ', '?', 0, count($values));
    }
    
    /**
     * Clear the properties with empty array.
     */
    protected function flush()
    {
        $this->sql = null;
        $this->columns = [];
        $this->values = [];
        $this->data = [];
    }
    
    /**
     * Get the current cached sql.
     * 
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
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