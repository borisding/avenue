<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Database\QueryBuilderTrait;
use Avenue\Interfaces\Database\CommandInterface;

class Command implements CommandInterface
{
    use QueryBuilderTrait;

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
        'bothAll'  => 'both',
        'objAll'   => 'obj',
        'numAll'   => 'num',
        'assocAll' => 'assoc',
        'bothOne'  => 'both',
        'objOne'   => 'obj',
        'numOne'   => 'num',
        'assocOne' => 'assoc'
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
     * Command for prepared statement.
     * Default for master database connection.
     *
     * @param  mixed  $sql
     * @param  boolean $slave
     * @return \Avenue\Database\Command
     */
    public function cmd($sql, $slave = false)
    {
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
    public function all($type = 'assoc')
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
    public function one($type = 'assoc')
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
    public function classOne($name, array $ctorArgs = [])
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
    public function classAll($name, array $ctorArgs = [])
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
    public function column($number = 0)
    {
        $this->run();
        return $this->statement->fetchColumn($number);
    }

    /**
     * Fetch and get total number of records.
     *
     * @return integer
     */
    public function totalRows()
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
    public function insertedId()
    {
        return $this->connection->getPdo()->lastInsertId();
    }

    /**
     * Get the number of affected rows.
     *
     * @return integer
     */
    public function affectedRows()
    {
        return $this->statement->rowCount();
    }

    /**
     * Debug by passing sql statement and data.
     * Print out raw SQL with actual value(s).
     *
     * @param  mixed $sql
     * @param  array  $data
     * @return string
     */
    public function debug($sql, array $data)
    {
        foreach ($data as $param => $value) {

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
     * `$this->cmd('...')->fetchObjAll()` is same with `$this->cmd('...')->all('obj')`
     *
     * for single record as object
     * `$this->cmd('...')->fetchObjOne()` is same with `$this->cmd('...')->one('obj')`
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
            return $this->one($this->fetchAlias[$method]);
        }

        // for multiple records
        return $this->all($this->fetchAlias[$method]);
    }
}
