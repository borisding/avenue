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
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::findAll()
     */
    public function findAll($params = null)
    {
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::save()
     */
    public function save($id = null)
    {
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::create()
     */
    public function create()
    {
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::update()
     */
    public function update($params = null)
    {
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\StreetInterface::remove()
     */
    public function remove($params = null)
    {
        
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