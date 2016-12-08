<?php
namespace Avenue\Database;

trait QueryBuilderTrait
{
    /**
     * SQL statement.
     *
     * @var string
     */
    private $sql;

    /**
     * SQL params data.
     *
     * @var array
     */
    private $data = [];

    /**
     * SQL where keyword exist.
     *
     * @var boolean
     */
    private $whereExist = false;

    /**
     * Select clause builder. Default select all.
     * Multiple columns can be passed as indexed array.
     *
     * @param  mixed $columns
     * @return $this
     */
    public function select($columns = '*')
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->setSql(sprintf('%s %s ', 'SELECT', $columns));
        return $this;
    }

    /**
     * Select count clause builder. Default count all.
     * Passed as associative array if count with alias name.
     *
     * @param  mixed $columns
     * @return $this
     */
    public function selectCount($columns = '*')
    {
        $this->setSql(
            (is_array($columns))
            ? sprintf('SELECT COUNT(%s) AS %s ', key($columns), current($columns))
            : sprintf('SELECT COUNT(%s) ', $columns)
        );

        return $this;
    }

    /**
     * Select distinct clause builder.
     * Multiple columns can be passed as indexed array.
     *
     * @param  mixed $columns
     * @return $this
     */
    public function selectDistinct($columns)
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->setSql(sprintf('%s %s ', 'SELECT DISTINCT', $columns));
        return $this;
    }

    /**
     * From clause builder.
     * Table with alias passed as associative array.
     *
     * @param  mixed $table
     * @return $this
     */
    public function from($table)
    {
        $this->setSql(
            (is_array($table))
            ? sprintf('%s %s AS %s', 'FROM', key($table), current($table))
            : sprintf('%s %s', 'FROM', $table)
        );

        return $this;
    }

    /**
     * Insert statement builder with key/value input.
     *
     * @param mixed $table
     * @param  array  $columns
     * @return $this
     */
    public function insert($table, array $columns)
    {
        $input = array_values($columns);
        $this->setSql(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', array_keys($columns)),
            $this->unnamedParams($input)
        ));

        $this->setData($input);
        return $this;
    }

    /**
     * Update statement builder with key/value input.
     *
     * @param mixed $table
     * @param  array  $columns
     * @return $this
     */
    public function update($table, array $columns)
    {
        $input = array_values($columns);
        $this->setSql(sprintf(
            'UPDATE %s SET %s',
            $table,
            implode(' = ?, ', array_keys($columns)) . ' = ?'
        ));

        $this->setData($input);
        return $this;
    }

    /**
     * Delete clause builder.
     *
     * @param  mixed $table
     * @return $this
     */
    public function delete($table)
    {
        $this->setSql(sprintf('%s %s', 'DELETE FROM', $table));
        return $this;
    }

    /**
     * Where clause builder. Accept up to 3 parameters.
     *
     * @return $this
     */
    public function where()
    {
        $args = func_get_args();
        $numArgs = count($args);
        $where = 'WHERE';

        if ($numArgs === 0 || $numArgs > 3) {
            throw new \InvalidArgumentException(
                sprintf('Invalid number of arguments for [%s] method', __FUNCTION__)
            );
        }

        if (!$this->whereExist) {
            $this->setSql(sprintf(' %s ', $where));
            $this->whereExist = true;
        }

        // with helper or callback function for grouping
        if ($numArgs === 1) {

            if (is_callable($args[0])) {
                $this->setSql('(');
                $args[0]($this);
                $this->setSql(')');
            } else {
                $this->setSql($args[0]);
            }
        // default `=` operator
        } elseif ($numArgs === 2) {
            $this->setSql(sprintf('%s %s ?', $args[0], '='));
            $this->setData($args[1]);
        // third is an indexed array
        } elseif ($numArgs === 3 && is_array($args[2])) {
            $this->setSql(sprintf('%s %s (%s)', $args[0], $args[1], $this->unnamedParams($args[2])));
            $this->setData($args[2]);
        // with specified operator
        } else {
            $this->setSql(sprintf('%s %s ?', $args[0], $args[1]));
            $this->setData($args[2]);
        }

        return $this;
    }

    /**
     * And where clause.
     *
     * @return $this
     */
    public function andWhere()
    {
        $this->setSql(sprintf(' %s ', 'AND'));
        return call_user_func_array([$this, 'where'], func_get_args());
    }

    /**
     * Or where clause.
     *
     * @return $this
     */
    public function orWhere()
    {
        $this->setSql(sprintf(' %s ', 'OR'));
        return call_user_func_array([$this, 'where'], func_get_args());
    }

    /**
     * Order by clause.
     *
     * @param  mixed  $columns
     * @return $this
     */
    public function orderBy($columns)
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->setSql(sprintf(' %s %s', 'ORDER BY', $columns));
        return $this;
    }

    /**
     * Limit clause builder.
     *
     * @param  mixed $rows
     * @param  mixed $from
     * @return $this
     */
    public function limit($rows, $from = null)
    {
        $this->setSql(sprintf(' %s ?', 'LIMIT'));
        $this->setData($rows);

        if (!empty($from)) {
            $this->offset($from);
        }

        return $this;
    }

    /**
     * Offset clause builder.
     *
     * @param mixed $from
     * @return $this
     */
    public function offset($from)
    {
        $this->setSql(sprintf(' %s ?', 'OFFSET'));
        $this->setData($from);

        return $this;
    }

    /**
     * Group by clause builder.
     *
     * @param  mixed  $columns
     * @return $this
     */
    public function groupBy($columns)
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->setSql(sprintf(' %s %s', 'GROUP BY', $columns));
        return $this;
    }

    /**
     * Having clause builder.
     *
     * @param  mixed $aggregate
     * @param  mixed $operator
     * @param  mixed $input
     * @return $this
     */
    public function having($aggregate, $operator, $input)
    {
        $this->setSql(sprintf(' %s %s %s ?', 'HAVING', $aggregate, $operator));
        $this->setData($input);

        return $this;
    }

    /**
     * Union clause builder based on the built SQLs.
     *
     * @param  array  $sqls
     * @return $this
     */
    public function union(array $sqls)
    {
        $this->setSql(implode(' UNION ', $sqls));
        return $this;
    }

    /**
     * Alternative of inner join builder.
     *
     * @param  mixed $table
     * @param  array  $on
     * @return $this
     */
    public function join($table, array $on)
    {
        $this->buildJoinClause('JOIN', $table, $on);
        return $this;
    }

    /**
     * Inner join clause builder.
     *
     * @param  mixed $table
     * @param  array  $on
     * @return $this
     */
    public function innerJoin($table, array $on)
    {
        $this->buildJoinClause('INNER JOIN', $table, $on);
        return $this;
    }

    /**
     * Left join clause builder.
     *
     * @param  mixed $table
     * @param  array  $on
     * @return $this
     */
    public function leftJoin($table, array $on)
    {
        $this->buildJoinClause('LEFT OUTER JOIN', $table, $on);
        return $this;
    }

    /**
     * Right join clause builder.
     *
     * @param  mixed $table
     * @param  array  $on
     * @return $this
     */
    public function rightJoin($table, array $on)
    {
        $this->buildJoinClause('RIGHT OUTER JOIN', $table, $on);
        return $this;
    }

    /**
     * Full join clause builder.
     *
     * @param  mixed $table
     * @param  array  $on
     * @return $this
     */
    public function fullJoin($table, array $on)
    {
        $this->buildJoinClause('FULL OUTER JOIN', $table, $on);
        return $this;
    }

    /**
     * Build join clause based on the type with provided arguments.
     *
     * @param  mixed $type
     * @param  mixed $table
     * @param  array  $on
     * @return $this
     */
    private function buildJoinClause($type, $table, array $on)
    {
        $column1 = key($on);
        $column2 = current($on);

        $this->setSql(sprintf(' %s %s ON %s = %s', $type, $table, $column1, $column2));
        return $this;
    }

    /**
     * Like clause helper.
     *
     * @param  mixed $column
     * @param  mixed $input
     * @return string
     */
    public function like($column, $input)
    {
        return $this->buildLikeClause('LIKE', $column, $input);
    }

    /**
     * Not like clause helper.
     *
     * @param  mixed $column
     * @param  mixed $input
     * @return string
     */
    public function notLike($column, $input)
    {
        return $this->buildLikeClause('NOT LIKE', $column, $input);
    }

    /**
     * Build like clause based on the type with provided arguments.
     *
     * @param  mixed $type
     * @param  mixed $column
     * @param  mixed  $input
     * @return string
     */
    private function buildLikeClause($type, $column, $input)
    {
        $this->setData($input);
        return sprintf('%s %s ?', $column, $type);
    }

    /**
     * In clause helper.
     *
     * @param  mixed $column
     * @param  array  $input
     * @return string
     */
    public function in($column, array $input)
    {
        return $this->buildInClause('IN', $column, $input);
    }

    /**
     * Not in clause helper.
     *
     * @param  mixed $column
     * @param  array  $input
     * @return string
     */
    public function notIn($column, array $input)
    {
        return $this->buildInClause('NOT IN', $column, $input);
    }

    /**
     * Build in clause based on the type with provided arguments.
     *
     * @param  mixed $type
     * @param  mixed $column
     * @param  array  $input
     * @return string
     */
    private function buildInClause($type, $column, array $input)
    {
        $this->setData($input);
        return sprintf('%s %s (%s)', $column, $type, $this->unnamedParams($input));
    }

    /**
     * Between clause helper.
     *
     * @param  mixed $column
     * @param  array $inputs
     * @return string
     */
    public function between($column, array $inputs)
    {
        return $this->buildBetweenClause('BETWEEN', $column, $inputs);
    }

    /**
     * Not between clause helper.
     *
     * @param  mixed $column
     * @param  array $inputs
     * @return string
     */
    public function notBetween($column, array $inputs)
    {
        return $this->buildBetweenClause('NOT BETWEEN', $column, $inputs);
    }

    /**
     * Build between clause based on the type with provided arguments.
     *
     * @param  mixed $type
     * @param  mixed $column
     * @param  array  $inputs
     * @return string
     */
    private function buildBetweenClause($type, $column, array $inputs)
    {
        if (count($inputs) !== 2) {
            throw new \InvalidArgumentException('Invalid length of inputs for between clause.');
        }

        $this->setData($inputs);
        return sprintf('%s %s ? %s ?', $column, $type, 'AND');
    }

    /**
     * Is null clause helper.
     *
     * @param  mixed  $column
     * @return string
     */
    public function isNull($column)
    {
        return $this->buildNullClause('IS NULL', $column);
    }

    /**
     * Is not null clause helper.
     *
     * @param  mixed  $column
     * @return string
     */
    public function isNotNull($column)
    {
        return $this->buildNullClause('IS NOT NULL', $column);
    }

    /**
     * Build null clause based on the type with provided arguments.
     *
     * @param  mixed $type
     * @param  mixed $column
     * @param  array  $inputs
     * @return string
     */
    private function buildNullClause($type, $column)
    {
        return sprintf('%s %s', $column, $type);
    }

    /**
     * Prepare query statement and reset data.
     *
     * @param  boolean $slave
     * @return $this
     */
    public function query($slave = true)
    {
        return $this->cmd($this->sql, $slave === true)
        ->batch($this->data)
        ->reset();

        return $this;
    }

    /**
     * Execute query by running sql statement while binding input data.
     * Clear stored data and sql statement string once executed.
     *
     * @return boolean
     */
    public function execute()
    {
        return $this->cmd($this->sql)->batch($this->data)->reset()->run();
    }

    /**
     * Assign input to data list based on the provided input.
     *
     * @param  mixed $input
     */
    public function setData($input)
    {
        if (is_array($input)) {
            $this->data = array_merge($this->data, $input);
        } else {
            array_push($this->data, $input);
        }
    }

    /**
     * Return current stored data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Build sql statement with clause provided.
     *
     * @param  mixed $clause
     * @return string
     */
    public function setSql($clause)
    {
        return $this->sql .= $clause;
    }

    /**
     * Return current built sql and clear it.
     *
     * @return string
     */
    public function getSql()
    {
        $sql = $this->sql;
        $this->sql = '';
        $this->whereExist = false;

        return $sql;
    }

    /**
     * Return the filled unnamed parameters based on the values.
     *
     * @param  array  $values
     * @return string
     */
    public function unnamedParams(array $values)
    {
        return $this->app->fillRepeat('?', ', ', 0, count($values));
    }

    /**
     * Reset persisted sql statement and data.
     * @return $this;
     */
    public function reset()
    {
        $this->sql = '';
        $this->data = [];
        $this->whereExist = false;

        return $this;
    }
}
