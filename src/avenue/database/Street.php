<?php
namespace Avenue\Database;

use Avenue\Database\PdoAdapter;
use Avenue\Database\StreetInterface;
use Avenue\Database\StreetJoinTrait;
use Avenue\Database\StreetRelationTrait;

class Street extends PdoAdapter implements StreetInterface
{
    use StreetJoinTrait;
    use StreetRelationTrait;
    
    /**
     * Targeted table.
     * 
     * @var mixed
     */
    protected $table;
    
    /**
     * Table primary key.
     * 
     * @var mixed
     */
    protected $pk;
    
    /**
     * Table foreign key.
     * 
     * @var mixed
     */
    protected $fk;
    
    /**
     * SQL statement.
     * 
     * @var mixed
     */
    private $sql;
    
    /**
     * Bind table columns.
     * 
     * @var array
     */
    private $columns = [];
    
    /**
     * Bind respective assigned values.
     * 
     * @var array
     */
    private $values = [];
    
    /**
     * Bind data values.
     * 
     * @var array
     */
    private $data = [];
    
    /**
     * Street class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->defineTable()->definePrimaryKey();
    }
    
    /**     
     * Select statement preparation.
     * Columns are optional.
     * 
     * @see \Avenue\Database\StreetInterface::find()
     */
    public function find(array $columns = [])
    {
        $this->columns = $columns;
        
        if (is_array($this->columns) && !empty($this->columns)) {
            $this->sql = sprintf('SELECT %s FROM %s', implode(', ', $this->columns), $this->table);
        } else {
            $this->sql = sprintf('SELECT * FROM %s', $this->table);
        }
        
        return $this;
    }
    
    /**
     * Shortcut of finding all records.
     * Columns are optional.
     * 
     * @see \Avenue\Database\StreetInterface::findAll()
     */
    public function findAll(array $columns = [])
    {
        return $this->find($columns)->getAll();
    }
    
    /**
     * Shortcut of finding one record.
     * Columns are optional.
     * 
     * @see \Avenue\Database\StreetInterface::findOne()
     */
    public function findOne(array $columns = [])
    {
        return $this->find($columns)->getOne();
    }
    
    /**
     * Select count statement.
     * Default is count all with total as alias.
     * 
     * @see \Avenue\Database\StreetInterface::findCount()
     */
    public function findCount($column = '*', $alias = 'total')
    {
        if (empty($column)) {
            throw new \InvalidArgumentException('Column name is not specified!');
        }
        
        if (empty($alias)) {
            throw new \InvalidArgumentException('Alias is not specified!');
        }
        
        $this->sql = sprintf('SELECT COUNT(%s) AS %s FROM %s', $column, $alias, $this->table);
        return $this;
    }
    
    /**
     * Select distinct statement.
     * 
     * @see \Avenue\Database\StreetInterface::findDistinct()
     */
    public function findDistinct(array $columns)
    {
        $this->columns = $columns;
        $this->sql = sprintf('SELECT DISTINCT %s FROM %s', implode(', ', $this->columns), $this->table);
        
        return $this;
    }
    
    /**
     * Get all found record(s) via union by passing respective model objects
     * 
     * @see \Avenue\Database\StreetInterface::findUnion()
     */
    public function findUnion(array $objects)
    {
        $queries = [];
        $values = [];
        $i = 0;
        
        // iterate each model objects
        // store respective sql and values for placeholders, if any
        while ($i < count($objects)) {
            $that = $objects[$i];
            array_push($queries, $that->sql);
            $values = array_merge($values, $that->values);
            $i++;
        }
        
        // compute populated queries and values
        // so that can be invoked via prepared statement from the model that calls it
        $this->sql = '(' . implode(') UNION (', $queries) . ')';
        $this->values = $values;
        
        unset($that, $queries, $values);
        return $this;
    }
    
    /**
     * Where condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::where()
     */
    public function where()
    {
        list($column, $operator, $value) = $this->getConditionParams(func_get_args());
        
        if (is_array($value)) {
            $values = $value;
            $this->whereIn($column, $values);
        } else {
            $this->sql .= sprintf(' WHERE %s %s %s', $column, $operator, '?');
            array_push($this->values, $value);
        }
        
        return $this;
    }
    
    /**
     * Where between method.
     * Alternative of greater than equal and less than equal.
     * 
     * @see \Avenue\Database\StreetInterface::whereBetween()
     */
    public function whereBetween($column, $first, $second)
    {
        $this->sql .= sprintf(' WHERE %s BETWEEN %s AND %s ', $column, $first, $second);
        array_push($this->values, $first, $second);
        
        return $this;
    }
    
    /**
     * Where and condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::andWhere()
     */
    public function andWhere()
    {
        $this->sql .= sprintf(' %s ', 'AND');
        list($column, $operator, $value) = $this->getConditionParams(func_get_args());
        
        if (is_array($value)) {
            $values = $value;
            $this->sql .= $this->getIn($column, $values);
        } else {
            $this->sql .= sprintf('%s %s %s', $column, $operator, '?');
            array_push($this->values, $value);
        }
        
        return $this;
    }
    
    /**
     * Where or condition accepts column and its value.
     * 
     * @see \Avenue\Database\StreetInterface::orWhere()
     */
    public function orWhere()
    {
        $this->sql .= sprintf(' %s ', 'OR');
        list($column, $operator, $value) = $this->getConditionParams(func_get_args());
        
        if (is_array($value)) {
            $values = $value;
            $this->sql .= $this->getIn($column, $values);
        } else {
            $this->sql .= sprintf('%s %s %s', $column, $operator, '?');
            array_push($this->values, $value);
        }
        
        return $this;
    }
    
    /**
     * Where in statement for multiple values.
     * 
     * @see \Avenue\Database\StreetInterface::whereIn()
     */
    public function whereIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $this->sql .= sprintf(' WHERE %s IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }
    
    /**
     * Where not in statement for multiple values.
     * 
     * @see \Avenue\Database\StreetInterface::whereNotIn()
     */
    public function whereNotIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $this->sql .= sprintf(' WHERE %s NOT IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }
    
    /**
     * Get in statement.
     * 
     * @param mixed $column
     * @param array $values
     */
    protected function getIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $sql = sprintf(' %s IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
        
        return $sql;
    }
    
    /**
     * Get not in statement.
     * 
     * @param mixed $column
     * @param array $values
     */
    protected function getNotIn($column, array $values)
    {
        $placeholders = $this->getPlaceholders($values);
        $sql = sprintf(' %s NOT IN (%s)', $column, $placeholders);
        $this->values = array_merge($this->values, $values);
    
        return $sql;
    }
    
    /**
     * Group by column statement.
     * 
     * @see \Avenue\Database\StreetInterface::groupBy()
     */
    public function groupBy(array $columns)
    {
        $columns = implode(', ', $columns);
        
        if (!empty($columns)) {
            $this->sql .= sprintf(' %s %s', 'GROUP BY', $columns);
        }
        
        return $this;
    }
    
    /**
     * Having statement with condition.
     * 
     * @see \Avenue\Database\StreetInterface::having()
     */
    public function having()
    {
        list($column, $operator, $value) = $this->getConditionParams(func_get_args());
        $this->sql .= sprintf(' HAVING %s %s %s', $column, $operator, '?');
        array_push($this->values, $value);
        
        return $this;
    }
    
    /**
     * Order by the column(s) and sort type.
     * 
     * @see \Avenue\Database\StreetInterface::orderBy()
     */
    public function orderBy(array $sorting)
    {
        $sorting = implode(', ', $sorting);
        
        if (!empty($sorting)) {
            $this->sql .= sprintf(' %s %s', 'ORDER BY', $sorting);
        }
        
        return $this;
    }
    
    /**
     * Limit row offset statement.
     * 
     * @see \Avenue\Database\StreetInterface::limit()
     */
    public function limit($row, $offset = 0)
    {
        $this->sql .= sprintf(' %s %d, %d', 'LIMIT', $offset, $row);
        return $this;
    }
    
    /**
     * Return all found records, default in associative array.
     * 
     * @see \Avenue\Database\StreetInterface::getAll()
     */
    public function getAll($type = 'assoc')
    {
        $result = $this
        ->cmd($this->sql)
        ->batch($this->values)
        ->fetchAll($type);
        
        $this->flush();
        return $result;
    }
    
    /**
     * Get all the found records in object.
     * 
     * @see \Avenue\Database\StreetInterface::getAllObj()
     */
    public function getAllObj()
    {
        return $this->getAll('obj');
    }
    
    /**
     * Get all the found records in indexed.
     * 
     * @see \Avenue\Database\StreetInterface::getAllNum()
     */
    public function getAllNum()
    {
        return $this->getAll('num');
    }
    
    /**
     * Get all the found records in both associative and indexed.
     * 
     * @see \Avenue\Database\StreetInterface::getAllBoth()
     */
    public function getAllBoth()
    {
        return $this->getAll('both');
    }
    
    /**
     * Return one record in associative array.
     * 
     * @see \Avenue\Database\StreetInterface::getOne()
     */
    public function getOne($type = 'assoc')
    {
        $result = $this
        ->cmd($this->sql)
        ->batch($this->values)
        ->fetchOne($type);
        
        $this->flush();
        return $result;
    }
    
    /**
     * Get one found record in object.
     * 
     * @see \Avenue\Database\StreetInterface::getOneObj()
     */
    public function getOneObj()
    {
        return $this->getOne('obj');
    }
    
    /**
     * Get a record in indexed.
     * 
     * @see \Avenue\Database\StreetInterface::getOneNum()
     */
    public function getOneNum()
    {
        return $this->getOne('num');
    }
    
    /**
     * Get a record in both associative and indexed.
     */
    public function getOneBoth()
    {
        return $this->getOne('both');
    }
    
    /**
     * Remove record(s) based on the passed in ID(s)
     * 
     * @see \Avenue\Database\StreetInterface::remove()
     */
    public function remove($id)
    {
        $this->sql = sprintf('DELETE FROM %s', $this->table);
        $this->where($this->pk, $id);
        
        $this
        ->cmd($this->sql)
        ->batch($this->values)
        ->run();
        
        $this->flush();
        return true;
    }
    
    /**
     * Remove all records.
     * 
     * @see \Avenue\Database\StreetInterface::removeAll()
     */
    public function removeAll()
    {
        $this->sql = sprintf('DELETE FROM %s', $this->table);
        $this->cmd($this->sql)->run();
        $this->flush();
        
        return true;
    }
    
    /**
     * Save by either creating or updating record(s) based on the ID(s).
     * 
     * @see \Avenue\Database\StreetInterface::save()
     */
    public function save($id = null)
    {
        if (!empty($id)) {
            return $this->update($id);
        } else {
            return $this->create();
        }
    }
    
    /**
     * Create new record into database.
     * Last inserted ID will be returned.
     * 
     * @see \Avenue\Database\StreetInterface::create()
     */
    public function create()
    {
        $this->columns = implode(', ', array_keys($this->data));
        $this->values = array_values($this->data);
        
        $placeholders = $this->getPlaceholders($this->values);
        $this->sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, $this->columns, $placeholders);
        
        $this
        ->cmd($this->sql)
        ->batch($this->values)
        ->run();
        
        $this->flush();
        return $this->getInsertedId();
    }
    
    /**
     * Update record based on the passed in ID.
     * ID can be a list or a value.
     * 
     * @see \Avenue\Database\StreetInterface::update()
     */
    public function update($id)
    {
        $this->columns = implode(' = ?, ', array_keys($this->data)) . ' = ?';
        $this->values = array_values($this->data);
        
        $this->sql = sprintf('UPDATE %s SET %s', $this->table, $this->columns);
        $this->where($this->pk, $id);
        
        $this
        ->cmd($this->sql)
        ->batch($this->values)
        ->run();
        
        $this->flush();
        return $this->getTotalRows();
    }
    
    /**
     * Update if row already exisiting or do insert instead if no row exist. 
     * 
     * @see \Avenue\Database\StreetInterface::upsert()
     */
    public function upsert($id)
    {
        if ($this->getCurrentPdoDriver() === 'mysql') {
            $this->data[$this->pk] = $id;
            $this->columns = implode(', ', array_keys($this->data));
            $this->values = array_values($this->data);
            
            $placeholders = $this->getPlaceholders($this->values);
            $this->sql = sprintf('REPLACE INTO %s (%s) VALUES (%s)', $this->table, $this->columns, $placeholders);
            
            $this
            ->cmd($this->sql)
            ->batch($this->values)
            ->run();
            
            $this->flush();
        } else {
            // temp store for later usage
            $data = $this->data;
            $result = $this->findCount()->where($this->pk, $id)->getOne();
            
            // reassign after data cleared
            $this->data = $data;
            
            if (isset($result['total']) && $result['total']) {
                $this->update($id);
            } else {
                $this->create();
            }
            
            unset($data, $result);
        }
        
        return true;
    }
    
    /**
     * Get the conditional params with operator inserted, if any.
     * 
     * @param array $params
     * @throws \InvalidArgumentException
     */
    protected function getConditionParams(array $params)
    {
        $numParams = count($params);
        
        if ($numParams < 2 || $numParams > 3) {
            throw new \InvalidArgumentException('Invalid number of parameters for condition.');
        }
        
        // default is equal sign by inserting as second element
        if ($numParams === 2) {
            array_splice($params, 1, 0, '=');
        }
        
        return $params;
    }
    
    /**
     * Define the table name based on the model class name.
     * Wrapped with the table prefix syntax.
     */
    protected function defineTable()
    {
        // assume table name is based on the defined model class name
        // unless it is clearly defined in model class with $table property itself
        if (empty($this->table)) {
            $this->table = strtolower($this->getModelName());
        }
        
        $this->table = '{' . $this->table . '}';
        return $this;
    }
    
    /**
     * Define the primary key of the table.
     * If none is defined, use default 'id' instead.
     */
    protected function definePrimaryKey()
    {
        if (empty($this->pk)) {
            $this->pk = 'id';
        }
        
        return $this;
    }
    
    /**
     * Get the model class name without the namespace.
     */
    protected function getModelName()
    {
        $model = $namespace = get_class($this);
        
        if (strpos($namespace, '\\') !== false) {
            $arrNamespace = explode('\\', $namespace);
            $model = array_pop($arrNamespace);
        }
        
        return $model;
    }
    
    /**
     * Get the placeholders based on the values.
     * 
     * @param array $values
     */
    protected function getPlaceholders(array $values = [])
    {
        return $this->app->arrFillJoin(', ', '?', 0, count($values));
    }
    
    /**
     * Clear the properties with empty array.
     */
    protected function flush()
    {
        $this->sql = '';
        $this->columns = [];
        $this->values = [];
        $this->data = [];
    }
    
    /**
     * Get the current cached sql.
     * 
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }
    
    /**
     * Get the current table name without the table prefix.
     *
     * @see \Avenue\Database\StreetInterface::getTable()
     */
    public function getTable()
    {
        return $this->replaceTablePrefix(($this->table));
    }
    
    /**
     * Get the current primary key.
     *
     * @see \Avenue\Database\StreetInterface::getPk()
     */
    public function getPk()
    {
        return $this->pk;
    }
    
    /**
     * Get the current model foreign key.
     *
     * @see \Avenue\Database\StreetInterface::getFk()
     */
    public function getFk()
    {
        return $this->fk;
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