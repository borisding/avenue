<?php
namespace Avenue\Database;

trait CommandWrapperTrait
{
    /**
     * Select all query wrapper with/without clause and parameters from master.
     *
     * @param mixed $clause
     * @param mixed $params
     * @param string $type
     */
    public function selectAll($clause = null, $params = null, $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectAllClause(),
            $clause,
            $params,
            $type,
            true
        );
    }

    /**
     * Select all query wrapper with/without clause and parameters from slave.
     *
     * @param mixed $clause
     * @param mixed $params
     * @param string $type
     */
    public function selectAllSlave($clause = null, $params = null, $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectAllClause(),
            $clause,
            $params,
            $type,
            false
        );
    }

    /**
     * Select column(s) query wrapper with/without clause and parameters from master.
     *
     * @param array $columns
     * @param mixed $clause
     * @param mixed $params
     * @param string $type
     */
    public function select(array $columns, $clause = null, $params = null, $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectClause($columns),
            $clause,
            $params,
            $type,
            true
        );
    }

    /**
     * Select column(s) query wrapper with/without clause and parameters from slave.
     *
     * @param array $columns
     * @param mixed $clause
     * @param mixed $params
     * @param string $type
     */
    public function selectSlave(array $columns, $clause = null, $params = null, $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectClause($columns),
            $clause,
            $params,
            $type,
            false
        );
    }

    /**
     * Return select all clause.
     *
     * @return string
     */
    private function getSelectAllClause()
    {
        return sprintf('select * from %s', $this->table);
    }

    /**
     * Return select clause with accepted column(s).
     *
     * @param array $columns
     * @return string
     */
    private function getSelectClause(array $columns)
    {
        return sprintf('select %s from %s', implode(', ', $columns), $this->table);
    }

    /**
     * Select statement's with where clause builder.
     *
     * @param mixed $sql
     * @param mixed $values
     * @param array $clause
     * @param mixed $type
     * @param mixed $master
     */
    private function getSelectWhereClause($sql, $clause, $params, $type, $master)
    {
        if (!empty($clause)) {
            $sql .= sprintf(' where %s', $clause);
        }

        if (!empty($params) && !is_array($params)) {
            $params = (array)$params;
        }

        $query = $this->cmd($sql, $master === true);

        if (is_array($params)) {
            $query = $query->batch($params);
        }

        return $query->fetchAll($type);
    }

    /**
     * Insert query wrapper with accepted columns key/value pair values.
     *
     * @param array $columns
     */
    public function insert(array $columns)
    {
        $values = array_values($columns);
        $sql = sprintf(
            'insert into %s (%s) values (%s)',
            $this->table,
            implode(', ', array_keys($columns)),
            $this->getPlaceholders($values)
        );

        return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
    }

    /**
     * Delete all records query wrapper.
     */
    public function deleteAll()
    {
        return $this
        ->cmd(sprintf('delete from %s', $this->table))
        ->run();
    }

    /**
     * Delete query wrapper with clause and parameters.
     *
     * @param mixed $clause
     * @param mixed $params
     */
    public function delete($clause, $params = null)
    {
        $sql = sprintf('delete from %s where %s', $this->table, $clause);

        if (!empty($params) && !is_array($params)) {
            $params = (array)$params;
        }

        $query = $this->cmd($sql);

        if (is_array($params)) {
            $query = $query->batch($params);
        }

        return $query->run();
    }

    /**
     * Update all records query with accepted columns key/pair values.
     *
     * @param array $columns
     */
    public function updateAll(array $columns)
    {
        $values = array_values($columns);
        $sql = sprintf(
            'update %s set %s',
            $this->table,
            implode(' = ?, ', array_keys($columns)) . ' = ?'
        );

        return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
    }

    /**
     * Update query wrapper with accepted columns key/pair values, clause and params.
     *
     * @param array $columns
     * @param mixed $clause
     * @param mixed $params
     */
    public function update(array $columns, $clause, $params = null)
    {
        $values = array_values($columns);
        $sql = sprintf(
            'update %s set %s where %s',
            $this->table,
            implode(' = ?, ', array_keys($columns)) . ' = ?',
            $clause
        );

        if (!empty($params)) {

            if (!is_array($params)) {
                array_push($values, $params);
            } elseif ($this->app->arrIsIndex($params)) {
                $values = array_merge($values, $params);
            } elseif ($this->app->arrIsAssoc($params)) {

                foreach ($params as $param => $value) {
                    // replace first occurance with unnamed parameter to align
                    // reassgin sql and move forward
                    $position = strpos($sql, $param);

                    if ($position !== false) {
                        $sql = substr_replace($sql, '?', $position, strlen($param));
                        array_push($values, $value);
                    }
                }
            }
        }

        return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
    }

    /**
     * Return the filled placeholders based on the values.
     *
     * @param array $values
     */
    public function getPlaceholders(array $values)
    {
        return $this->app->fillRepeat('?', ', ', 0, count($values));
    }
}