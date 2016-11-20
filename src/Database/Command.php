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
     * SQL statement
     *
     * @var string
     */
    private $sql;

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
     * @return \Avenue\Database\Connection
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
     * @param mixed $name
     * @return \Avenue\Database\Command
     */
    public function setTable($name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * Return the mapped model's table name.
     *
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Update the default model's PK column name.
     *
     * @param mixed $name
     * @return \Avenue\Database\Command
     */
    public function setPk($name)
    {
        $this->pk = $name;
        return $this;
    }

    /**
     * Return the model's PK column name.
     *
     * @return mixed
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * Command for prepared statement.
     * Default for master database connection.
     *
     * @param  mixed  $sql
     * @param  boolean $slave
     * @return \Avenue\Database\Command
     */
    public function cmd($sql, $slave = false)
    {
        $this->sql = $sql;
        $this->statement = $this->connection->getPdo($slave)->prepare($sql);

        return $this;
    }

    /**
     * Alias command method for master prepared statement.
     *
     * @param  mixed $sql
     * @return object
     */
    public function cmdMaster($sql)
    {
        return $this->cmd($sql, false);
    }

    /**
     * Alias command method for slave prepared statement.
     *
     * @param  mixed $sql
     * @return object
     */
    public function cmdSlave($sql)
    {
        return $this->cmd($sql, true);
    }

    /**
     * Execute prepared statement.
     *
     * @return boolean
     */
    public function run()
    {
        return $this->statement->execute();
    }

    /**
     * Execute prepared statement with actual param values.
     *
     * @param  array  $params
     * @return boolean
     */
    public function runWith(array $params)
    {
        return $this->statement->execute($params);
    }

    /**
     * Fetch all records method.
     * Associative format is returned by default.
     *
     * @param  string $type
     * @return mixed
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
     * @param  string $type
     * @return mixed
     */
    public function fetchOne($type = 'assoc')
    {
        $this->withFetchMode($type)->run();
        return $this->statement->fetch();
    }

    /**
     * Fetch single records with class behavior.
     *
     * @param  mixed $name
     * @param  array $ctorArgs
     * @return mixed
     */
    public function fetchClassOne($name, array $ctorArgs = [])
    {
        $this->withClassModeRun($name, $ctorArgs);
        return $this->statement->fetch();
    }

    /**
     * Fetch multiple records with class behavior.
     *
     * @param  mixed $name
     * @param  array $ctorArgs
     * @return mixed
     */
    public function fetchClassAll($name, array $ctorArgs = [])
    {
        $this->withClassModeRun($name, $ctorArgs);
        return $this->statement->fetchAll();
    }

    /**
     * Fetch a column from the next row.
     *
     * @param  integer $number
     * @return mixed
     */
    public function fetchColumn($number = 0)
    {
        $this->run();
        return $this->statement->fetchColumn($number);
    }

    /**
     * Fetch and get total number of records.
     *
     * @return integer
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
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  boolean $reference
     * @return \Avenue\Database\Command
     */
    public function bind($key, $value, $reference = false)
    {
        $type = $this->getParamDataType($value);

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
     * @param  array  $params
     * @param  boolean $reference
     * @return Avenue\Database\Command
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

            foreach ($params as $column => $value) {
                $this->bind(++$column, $value, $reference);
            }
        }

        return $this;
    }

    /**
     * Begin transaction.
     *
     * @return mixed
     */
    public function begin()
    {
        return $this->connection->getPdo()->beginTransaction();
    }

    /**
     * End transaction with commit.
     *
     * @return mixed
     */
    public function end()
    {
        return $this->connection->getPdo()->commit();
    }

    /**
     * Cancel transaction with rollback.
     *
     * @return mixed
     */
    public function cancel()
    {
        return $this->connection->getPdo()->rollBack();
    }

    /**
     * Get inserted ID.
     *
     * @return mixed
     */
    public function getInsertedId()
    {
        return $this->connection->getPdo()->lastInsertId();
    }

    /**
     * Get the number of affected rows.
     *
     * @return integer
     */
    public function getAffectedRows()
    {
        return $this->statement->rowCount();
    }

    /**
     * Debug by printing out raw SQL with actual value(s)
     *
     * @return mixed
     */
    public function debug(array $params = [])
    {
        $sql = $this->sql;

        foreach ($params as $param => $value) {

            if (is_string($value)) {
                $value = sprintf("'%s'", $value);
            } elseif (is_bool($value)) {
                $value = $value ? 'TRUE' : 'FALSE';
            } elseif (is_null($value)) {
                $value = 'NULL';
            }

            if (is_int($param)) {
                $sql = preg_replace('/\?/', $value, $sql, 1);
            } else {
                $sql = str_replace($param, $value, $sql);
            }
        }

        return sprintf('[SQL] %s', $sql);
    }

    /**
     * Set class mode and run targeted class.
     *
     * @param  mixed $name
     * @param  array $ctorArgs
     * @return mixed
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
     * @param  mixed $type
     * @param  mixed $className
     * @param  array $ctorArgs
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
     * Decide and get the param data type of passed value.
     *
     * @param  mixed $value
     * @return integer
     */
    private function getParamDataType($value)
    {
        switch ($value) {
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
     * @return mixed
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
