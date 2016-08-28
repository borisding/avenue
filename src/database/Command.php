<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Interfaces\Database\CommandInterface;

class Command implements CommandInterface
{
    /**
     * App class instance.
     *
     * @var object
     */
    protected $app;

    /**
     * Table name of targeted model.
     *
     * @var mixed
     */
    protected $table;

    /**
     * Prepared statement.
     *
     * @var mixed
     */
    protected $statement;

    /**
     * Connection class instance.
     *
     * @var object
     */
    protected static $connect;

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
     * Fetch all alias method.
     *
     * @var array
     */
    protected $fetchAllAlias = [
        'fetchAllBoth'  => 'both',
        'fetchAllObj'   => 'obj',
        'fetchAllNum'   => 'num',
        'fetchAllAssoc' => 'assoc'
    ];

    /**
     * Command class constructor.
     * Define table name if not specified.
     * Instantiate connection class.
     *
     * @param App $app
     * @return object
     */
    protected function __construct(App $app)
    {
        $this->app = $app;

        // assign table based on the class model if table is not specified
        if (empty($this->table)) {
            $this->table = $this->getTableName();
        }

        // instantiate connection
        if (empty(self::$connect)) {
            self::$connect = new Connection($this->app);
        }

        return self::$connect;
    }

    /**
     * Disconnect database connection.
     * This will trigger Connection class destructor.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::disconnect()
     */
    public function disconnect()
    {
        return self::$connect = null;
    }

    /**
     * Get PDO instance for master database.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getPdoMaster()
     */
    public function getPdoMaster()
    {
        return self::$connect->withMaster();
    }

    /**
     * Get PDO instance for slave database.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getPdoSlave()
     */
    public function getPdoSlave()
    {
        return self::$connect->withSlave();
    }

    /**
     * Command for prepared statement.
     * Default for master database.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cmd()
     */
    public function cmd($sql, $master = true)
    {
        $conn = ($master !== true) ? $this->getPdoSlave() : $this->getPdoMaster();
        $this->statement = $conn->prepare($sql);

        unset($conn);
        return $this;
    }

    /**
     * Alias command method for master prepared statement.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cmdMaster()
     */
    public function cmdMaster($sql)
    {
        return $this->cmd($sql, true);
    }

    /**
     * Alias command method for slave prepared statement.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cmdSlave()
     */
    public function cmdSlave($sql)
    {
        return $this->cmd($sql, false);
    }

    /**
     * Execute prepared statement.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::run()
     */
    public function run()
    {
        if (empty($this->statement)) {
            throw new \PDOException('PDO prepared statement is empty!');
        }

        $this->statement->execute();
        return $this;
    }

    /**
     * Fetch all records method.
     * Associative format is returned by default.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchAll()
     */
    public function fetchAll($type = 'assoc')
    {
        $this->getFetchMode($type)->run();
        return $this->statement->fetchAll();
    }

    /**
     * Fetch single record method.
     * Associative format is returned by default.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchOne()
     */
    public function fetchOne($type = 'assoc')
    {
        $this->getFetchMode($type)->run();
        return $this->statement->fetch();
    }

    /**
     * Fetch all records with class behavior.
     * Class name is required.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchClass()
     */
    public function fetchClass($name)
    {
        if (!class_exists($name)) {
            throw new \InvalidArgumentException(sprintf('Class [%s] does not exist!', $name));
        }

        $this->getFetchMode('class', $name)->run();
        return $this->statement->fetchAll();
    }

    /**
     * Fetch and get total number of records.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchTotalRows()
     */
    public function fetchTotalRows()
    {
        $this->run();
        return $this->statement->rowCount();
    }

    /**
     * Bind parameter(s) for prepared statement.
     * Default is binding with value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::bind()
     */
    public function bind($key, $value, $reference = false)
    {
        try {
            $type = $this->getParamScalar($value);

            if ($reference) {
                $this->statement->bindParam($key, $value, $type);
            } else {
                $this->statement->bindValue($key, $value, $type);
            }

            return $this;
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Bind parameter(s) for prepared statement in mass.
     * Default is binding with value.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::batch()
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
     * Begin transaction.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::begin()
     */
    public function begin()
    {
        return $this->getPdoMaster()->beginTransaction();
    }

    /**
     * End transaction with commit.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::end()
     */
    public function end()
    {
        return $this->getPdoMaster()->commit();
    }

    /**
     * Cancel transaction with rollback.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cancel()
     */
    public function cancel()
    {
        return $this->getPdoMaster()->rollBack();
    }

    /**
     * Get inserted ID.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getInsertedId()
     */
    public function getInsertedId()
    {
        return $this->getPdoMaster()->lastInsertId();
    }

    /**
     * Define table name for targeted model class if table name is not specified.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getTableName()
     */
    public function getTableName()
    {
        $model = $namespace = get_class($this);

        if (strpos($namespace, '\\') !== false) {
            $chunk = explode('\\', $namespace);
            $model = array_pop($chunk);
        }

        return strtolower($model);
    }

    /**
     * Shortcut for debug and dump params for prepared statement.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::ddp()
     */
    public function ddp()
    {
        return $this->statement->debugDumpParams();
    }

    /**
     * Deciding the fetch mode based on the fetch type.
     *
     * @param mixed $type
     * @param mixed $className
     * @return \Avenue\Database\Command
     */
    protected function getFetchMode($type, $className = null)
    {
        $fetchType = $this->app->arrGet($type, $this->fetchTypes, PDO::FETCH_ASSOC);

        if ($type === 'class' && !empty($className)) {
            $this->statement->setFetchMode($fetchType | PDO::FETCH_PROPS_LATE, $className);
        } else {
            $this->statement->setFetchMode($fetchType);
        }

        return $this;
    }

    /**
     * Decide and get the scalar type of passed value.
     *
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return number
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
     * Fetch all alias method via magic call method.
     * If none is found, throw invalid method exception.
     *
     * @param mixed $method
     * @param array $params
     */
    public function __call($method, array $params = [])
    {
        if (!isset($this->fetchAllAlias[$method])) {
            throw new \PDOException('Calling invalid method ['. $method .']');
        }

        return $this->fetchAll($this->fetchAllAlias[$method]);
    }
}