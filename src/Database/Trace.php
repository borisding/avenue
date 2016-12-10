<?php
namespace Avenue\Database;

use PDO;
use Avenue\App;
use Avenue\Database\Connection;
use Avenue\Database\CommandTrait;
use Avenue\Database\QueryBuilderTrait;
use Avenue\Interfaces\Database\TraceInterface;

class Trace implements TraceInterface
{
    use CommandTrait;
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
