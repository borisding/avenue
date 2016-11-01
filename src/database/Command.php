<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Database\CommandWrapperTrait;
use Avenue\Interfaces\Database\CommandInterface;

class Command implements CommandInterface
{
    use CommandWrapperTrait;

    /**
     * App class instance.
     *
     * @var \Avenue\App
     */
    protected $app;

    /**
     * Connection class instance.
     *
     * @var \Avenue\Database\Connection
     */
    protected $connection;

    /**
     * Table name of model.
     *
     * @var mixed
     */
    protected $table;

    /**
     * Default table primary key of model.
     *
     * @var string
     */
    protected $pk = 'id';

    /**
     * Prepared statement.
     *
     * @var mixed
     */
    private $statement;

    /**
     * Supported fetch types.
     *
     * @var array
     */
    private $fetchTypes = [
        'both' 	=> PDO::FETCH_BOTH,
        'obj'	=> PDO::FETCH_OBJ,
        'class'	=> PDO::FETCH_CLASS,
        'num'	=> PDO::FETCH_NUM,
        'assoc'	=> PDO::FETCH_ASSOC
    ];

    /**
     * Fetch alias methods.
     *
     * @var array
     */
    private $fetchAlias = [
        'fetchBothAll'  => 'both',
        'fetchObjAll'   => 'obj',
        'fetchNumAll'   => 'num',
        'fetchAssocAll' => 'assoc',
        'fetchBothOne'  => 'both',
        'fetchObjOne'   => 'obj',
        'fetchNumOne'   => 'num',
        'fetchAssocOne' => 'assoc'
    ];

    /**
     * Command class constructor.
     * Instantiate connection class and define table name if not specified.
     */
    public function __construct()
    {
        $this->app = App::getInstance();

        // create connection class instance if does not exist
        if (!$this->connection instanceof Connection) {
            $this->connection = new Connection($this->app);
        }

        // assign table based on the class model if table is not specified
        if (empty($this->table)) {
            $namespace = get_class($this);
            return $this->table = strtolower(substr($namespace, strrpos($namespace, '\\') + 1));
        }
    }

    /**
     * Return the current connection class instance.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getConnectionInstance()
     */
    public function getConnectionInstance()
    {
        if (!$this->connection instanceof Connection) {
            throw new \InvalidArgumentException('Failed to get connection class instance.');
        }

        return $this->connection;
    }

    /**
     * Update the mapped model's table name.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::setTable()
     */
    public function setTable($name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * Return the mapped model's table name.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getTable()
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Update the default model's PK column name.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::setPk()
     */
    public function setPk($name)
    {
        $this->pk = $name;
        return $this;
    }

    /**
     * Return the model's PK column name.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getPk()
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Command for prepared statement.
     * Default for master database connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cmd()
     */
    public function cmd($sql, $slave = false)
    {
        $this->statement = $this->connection->getPdo($slave)->prepare($sql);
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
        return $this->cmd($sql, false);
    }

    /**
     * Alias command method for slave prepared statement.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cmdSlave()
     */
    public function cmdSlave($sql)
    {
        return $this->cmd($sql, true);
    }

    /**
     * Execute prepared statement.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::run()
     */
    public function run()
    {
        return $this->statement->execute();
    }

    /**
     * Execute prepared statement with actual param values.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::runWith()
     */
    public function runWith(array $params)
    {
        return $this->statement->execute($params);
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
        $this->withFetchMode($type)->run();
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
        $this->withFetchMode($type)->run();
        return $this->statement->fetch();
    }

    /**
     * Fetch single records with class behavior.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchClassOne()
     */
    public function fetchClassOne($name, array $ctorArgs = [])
    {
        $this->withClassModeRun($name, $ctorArgs);
        return $this->statement->fetch();
    }

    /**
     * Fetch multiple records with class behavior.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchClassAll()
     */
    public function fetchClassAll($name, array $ctorArgs = [])
    {
        $this->withClassModeRun($name, $ctorArgs);
        return $this->statement->fetchAll();
    }

    /**
     * Fetch a column from the next row.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::fetchColumn()
     */
    public function fetchColumn($number = 0)
    {
        $this->run();
        return $this->statement->fetchColumn($number);
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
        $type = $this->getParamScalar($value);

        if ($reference) {
            $this->statement->bindParam($key, $value, $type);
        } else {
            $this->statement->bindValue($key, $value, $type);
        }

        return $this;
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
            $column = 1;

            foreach ($params as $value) {
                $this->bind($column, $value, $reference);
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
        return $this->connection->getPdo()->beginTransaction();
    }

    /**
     * End transaction with commit.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::end()
     */
    public function end()
    {
        return $this->connection->getPdo()->commit();
    }

    /**
     * Cancel transaction with rollback.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cancel()
     */
    public function cancel()
    {
        return $this->connection->getPdo()->rollBack();
    }

    /**
     * Get inserted ID.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getInsertedId()
     */
    public function getInsertedId()
    {
        return $this->connection->getPdo()->lastInsertId();
    }

    /**
     * Get the number of affected rows.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getAffectedRows()
     */
    public function getAffectedRows()
    {
        return $this->statement->rowCount();
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
     * Set class mode and run targeted class.
     *
     * @param mixed $name
     * @param array $ctorArgs
     * @throws \InvalidArgumentException
     */
    private function withClassModeRun($name, array $ctorArgs = [])
    {
        if (!class_exists($name)) {
            throw new \InvalidArgumentException(sprintf('Class [%s] does not exist!', $name));
        }

        return $this->withFetchMode('class', $name, $ctorArgs)->run();
    }

    /**
     * Deciding the fetch mode based on the fetch type.
     *
     * @param mixed $type
     * @param mixed $className
     * @param array $ctorArgs
     * @return \Avenue\Database\Command
     */
    private function withFetchMode($type, $className = null, array $ctorArgs = [])
    {
        $fetchType = $this->app->arrGet($type, $this->fetchTypes, PDO::FETCH_ASSOC);

        if ($type === 'class' && !empty($className)) {
            $this->statement->setFetchMode($fetchType|PDO::FETCH_PROPS_LATE, $className, $ctorArgs);
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
     * Fetch alias method via magic call method.
     * If none is found, throw invalid method exception.
     *
     * eg:
     * for multiple records as object
     *
     * `$this->cmd('...')->fetchObjAll()` is same with `$this->cmd('...')->fetchAll('obj')`
     *
     * for single record as object
     * `$this->cmd('...')->fetchObjOne()` is same with `$this->cmd('...')->fetchOne('obj')`
     *
     * @param mixed $method
     * @param array $params
     */
    public function __call($method, array $params = [])
    {
        if (!isset($this->fetchAlias[$method])) {
            throw new \PDOException('Calling invalid method ['. $method .']');
        }

        // for single record
        if (strtolower(substr($method, -3)) === 'one') {
            return $this->fetchOne($this->fetchAlias[$method]);
        }

        // for multiple records
        return $this->fetchAll($this->fetchAlias[$method]);
    }
}