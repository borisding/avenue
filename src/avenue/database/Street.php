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
        
        // assume table name is based on the defined model class name
        // unless it is clearly defined in model class with $table property itself
        if (empty($this->table)) {
            $this->setTableName();
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::findOne()
     */
    public function findOne($params = null)
    {
        try {
        
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::findAll()
     */
    public function findAll($params = null)
    {
        try {
        
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
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::remove()
     */
    public function remove($params = null)
    {
    
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
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
        
            $sql = 'INSERT INTO ' . $this->table . ' (' . $columns . ') ';
            $sql .= 'VALUES (' . $placeholders . ')';
            
            $this->cmd($sql)->batch($values)->run();
            $this->data = [];
            
            return $this->getInsertedId();
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Update record based on the passed in ID.
     * 
     * @param mixed $id
     * @throws \RuntimeException
     */
    private function update($id)
    {
        try {
            echo 'going to update record';
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * set the table name based on the model class name.
     * Wrapped with the table prefix syntax.
     */
    private function setTableName()
    {
        $this->table = strtolower($this->getModelName());
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