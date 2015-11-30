<?php
namespace Avenue\Database;

use Avenue\Database\PdoAdapter;
use Avenue\Database\StreetInterface;

class Street extends PdoAdapter implements StreetInterface
{
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
     * List of data values.
     * 
     * @var array
     */
    private $data = [];
    
    /**
     * Base model class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setTableName();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::findOne()
     */
    public function findOne()
    {
        try {
            // TODO
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::findAll()
     */
    public function findAll()
    {
        try {
            $args = func_get_args();
            $numArgs = count($args);
            
            if ($this->methodParamIsValid($args)) {
                // TODO
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::findQuery()
     */
    public function findQuery()
    {
        try {
            // TODO
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
        }
        
        return $this->create();
    }
    
    /**
     * Create new record into database.
     * Last inserted ID will be returned.
     * 
     * @throws \RuntimeException
     */
    private function create()
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
    private function update($id)
    {
        try {
            $columns = implode(' = ?, ', array_keys($this->data)) . ' = ?';
            $values = array_values($this->data);
            
            $sql = 'UPDATE ' . $this->table . ' SET ';
            $sql .= $columns . ' WHERE ' . $this->getPk();
            $sql .= $this->getWhereIdCondition($id);
            
            $this->cmd($sql)->batch($values)->run();
            $this->flush();
            
            return true;
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
            $sql .= ' WHERE ' . $this->getPk();
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
     * Check if number of arguments passed to method is valid,
     * and also, meet the condition.
     * 
     * @param mixed $args
     * @throws \InvalidArgumentException
     */
    private function methodParamIsValid($args)
    {
        // maximum 2 arguments
        if (count($args) > 2) {
            $fromMethod = debug_backtrace();
            $fn = $fromMethod[1]['function'];
            throw new \InvalidArgumentException('Maximum 2 arguments for [' . $fn .'].');
        }
        
        // second must be callable
        if (count($args) == 2 && !is_callable($args[1])) {
            throw new \InvalidArgumentException('Second argument must be a callable function.');
        }
        
        return true;
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
        // respective ids is escaped here
        
        if (is_array($id)) {
            $ids = $id;
            $ids = $this->app->escapeEach($ids);
            $ids = implode(', ', $ids);
            $condition = ' IN (' . $ids . ')';
        } else {
            $condition = ' = ' . $this->app->escape($id);
        }
        
        return $condition;
    }
    
    /**
     * set the table name based on the model class name.
     * Wrapped with the table prefix syntax.
     */
    private function setTableName()
    {
        // assume table name is based on the defined model class name
        // unless it is clearly defined in model class with $table property itself
        if (empty($this->table)) {
            $this->table = strtolower($this->getModelName());
        }
        
        $this->table = '{' . $this->table . '}';
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
     * Get the primary key of the table.
     * If none is set, return id as default.
     */
    private function getPk()
    {
        return (!empty($this->pk)) ? $this->pk : 'id';
    }
    
    /**
     * Clear the data property with empty array.
     */
    private function flush()
    {
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