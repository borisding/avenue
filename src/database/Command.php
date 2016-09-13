<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Interfaces\Database\CommandInterface;

class Command extends Connection implements CommandInterface
{
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
     * Define table name if not specified.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        // assign table based on the class model if table is not specified
        if (empty($this->table)) {
            $this->table = $this->getTableName();
        }
    }

    /**
     * Command for prepared statement.
     * Default for master database connection.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cmd()
     */
    public function cmd($sql, $master = true)
    {
        $conn = ($master !== true) ? $this->getSlavePdo() : $this->getMasterPdo();
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
        return $this->getMasterPdo()->beginTransaction();
    }

    /**
     * End transaction with commit.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::end()
     */
    public function end()
    {
        return $this->getMasterPdo()->commit();
    }

    /**
     * Cancel transaction with rollback.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::cancel()
     */
    public function cancel()
    {
        return $this->getMasterPdo()->rollBack();
    }

    /**
     * Get inserted ID.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getInsertedId()
     */
    public function getInsertedId()
    {
        return $this->getMasterPdo()->lastInsertId();
    }

    /**
     * Get the total rows.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getTotalRow()
     */
    public function getTotalRow()
    {
        return $this->statement->rowCount();
    }

    /**
     * Define table name for targeted model class if table name is not specified.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\Database\CommandInterface::getTableName()
     */
    public function getTableName()
    {
        $namespace = get_class($this);
        return strtolower(substr($namespace, strrpos($namespace, '\\') + 1));
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