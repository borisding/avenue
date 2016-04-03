<?php
namespace Avenue\Database;

use PDO;
use Avenue\Database\Connection;
use Avenue\Database\PdoAdapterInterface;

class PdoAdapter extends Connection implements PdoAdapterInterface
{
    /**
     * PDO connection object.
     *
     * @var mixed
     */
    protected $conn;

    /**
     * Query statement.
     *
     * @var mixed
     */
    protected $stmt;

    /**
     * Supported fetch types.
     *
     * @var array
     */
    protected $fetchTypes = [
        'both' 	=> PDO::FETCH_BOTH,
        'obj'	=> PDO::FETCH_OBJ,
        'class'	=> PDO::FETCH_CLASS,
        'num'	=> PDO::FETCH_NUM,
        'assoc'	=> PDO::FETCH_ASSOC
    ];

    /**
     * Fetch alias method.
     *
     * @var array
     */
    protected $fetchAlias = [
        'fetchBoth'  => 'both',
        'fetchObj'   => 'obj',
        'fetchNum'   => 'num',
        'fetchAssoc' => 'assoc'
    ];

    /**
     * PdoAdapter class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (empty($this->conn)) {
            $this->conn = $this->getDatabaseConnection();
        }
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
        // prepare the statement if stmt object is empty
        // by getting the persisted sql and bound values
        if (empty($this->stmt)) {
            $this->cmd($this->getSql())->batch($this->getValues());
        }

        $this->stmt->execute();

        // clear any persisted data
        $this->flush();
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::fetchAll()
     */
    public function fetchAll($type = 'assoc')
    {
        $this->getFetchMode($type)->run();
        return $this->stmt->fetchAll();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::fetchOne()
     */
    public function fetchOne($type = 'assoc')
    {
        $this->getFetchMode($type)->run();
        return $this->stmt->fetch();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::fetchClass()
     */
    public function fetchClass($name)
    {
        $this->getFetchMode('class', $name)->run();
        return $this->stmt->fetchAll();
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
        // for ':param' binding
        if ($this->app->arrIsAssoc($params)) {
            foreach ($params as $key => $value) {
                $this->bind($key, $value, $reference);
            }
        // for '?' binding
        } else {
            $i = 0;
            $column = 1;

            while ($i < count($params)) {
                $this->bind($column, $params[$i], $reference);
                $i++;
                $column++;
            }
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
     * @see \Avenue\Database\PdoAdapterInterface::cancel()
     */
    public function cancel()
    {
        return $this->conn->rollBack();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Database\PdoAdapterInterface::getTotalRows()
     */
    public function getTotalRows()
    {
        return $this->stmt->rowCount();
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
     * Deciding the fetch mode based on the fetch type.
     *
     * @param mixed $type
     * @param mixed $className
     * @return \Avenue\Database\PdoAdapter
     */
    protected function getFetchMode($type, $className = null)
    {
        $fetchType = $this->getFetchType($type);

        if (!empty($className) && $type === 'class') {
            $this->stmt->setFetchMode($fetchType | PDO::FETCH_PROPS_LATE, $className);
        } else {
            $this->stmt->setFetchMode($fetchType);
        }

        return $this;
    }

    /**
     * Get the fetch type based on the name.
     *
     * @param mixed $name
     */
    protected function getFetchType($name)
    {
        return $this->app->arrGet($name, $this->fetchTypes, PDO::FETCH_ASSOC);
    }

    /**
     * Decide and get the scalar type of passed value.
     *
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    protected function getParamScalar($value)
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
    protected function replaceTablePrefix($sql)
    {
        $sql = str_replace('{', $this->getTablePrefix(), $sql);
        $sql = str_replace('}', '', $sql);

        return $sql;
    }

    /**
     * Returning dump of prepared sql for debugging purpose.
     */
    public function ddp()
    {
        return $this->stmt->debugDumpParams();
    }

    /**
     * Fetch alias method via magic call method.
     * If none is found, throw invalid method exception.
     *
     * @param mixed $method
     * @param array $params
     */
    public function __call($method, array $params = [])
    {
        if (!isset($this->fetchAlias[$method])) {
            throw new \PDOException('Calling invalid method ['. $method .']');
        }

        return $this->fetchAll($this->fetchAlias[$method]);
    }
}