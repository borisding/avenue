<?php
namespace Avenue\Database;

use \PDO;
use Avenue\Database\Connection;
use Avenue\Database\PdoAdapterInterface;

class PdoAdapter extends Connection implements PdoAdapterInterface
{
    /**
     * PDO connection object.
     * 
     * @var mixed
     */
    private $conn;
    
    /**
     * Query statement.
     * 
     * @var mixed
     */
    private $stmt;
    
    /**
     * PdoAdapter class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->conn = $this->getDatabaseConnection();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::cmd()
     */
    public function cmd($sql)
    {
        $this->stmt = $this->conn->prepare($this->replaceTablePrefix($sql));
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::run()
     */
    public function run()
    {
        if (empty($this->stmt)) {
            throw new \PDOException('Failed to execute prepared statement.');
        }
        
        $this->stmt->execute();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::bind()
     */
    public function bind($key, $value, $reference = false)
    {
        try {
            $type = $this->getParamScalar($value);
            
            if ($reference) {
                $this->stmt->bindParam($key, $value, $type);
            } else {
                $this->stmt->bindValue($key, $value, $type);
            }
            
            return $this;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::batch()
     */
    public function batch(array $params = [], $reference = false)
    {
        if (!$this->app->arrIsAssoc($params)) {
            throw new \InvalidArgumentException('Parameters must be in associative array.');
        }
        
        foreach ($params as $key => $value) {
            $this->bind($key, $value, $reference);
        }
        
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::begin()
     */
    public function begin()
    {
        return $this->conn->beginTransaction();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::end()
     */
    public function end()
    {
        return $this->conn->commit();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::getInsertedId()
     */
    public function getInsertedId()
    {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Decide and get the scalar type of passed value.
     * 
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    private function getParamScalar($value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Failed to bind parameter. Invalid scalar type.');
        }
        
        switch($value) {
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            default:
                $type = PDO::PARAM_STR;
                break;
        }
        
        return $type;
    }
    
    /**
     * Replace curly brackets with table prefix, if any.
     * 
     * @param mixed $sql
     * @return mixed
     */
    private function replaceTablePrefix($sql)
    {
        $sql = str_replace('{', $this->getTablePrefix(), $sql);
        $sql = str_replace('}', '', $sql);
        
        return $sql;
    }
}