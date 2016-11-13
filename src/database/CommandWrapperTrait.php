<?php
namespace Avenue\Database;

trait CommandWrapperTrait
{
    /**
     * Select all query wrapper with/without clause and parameters from master.
     *
     * @param  mixed $clause
     * @param  mixed $params
     * @param  string $type
     * @return mixed
     */
    public function selectAll($clause = null, $params = null, $type = 'assoc')
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
     * Select all query wrapper with/without clause and parameters from slave.
     *
     * @param  mixed $clause
     * @param  mixed $params
     * @param  string $type
     * @return mixed
     */
    public function selectAllSlave($clause = null, $params = null, $type = 'assoc')
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
     * Select column(s) query wrapper with/without clause and parameters from master.
     *
     * @param  array  $columns
     * @param  mixed $clause
     * @param  mixed $params
     * @param  string $type
     * @return mixed
     */
    public function select(array $columns, $clause = null, $params = null, $type = 'assoc')
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
     * Select column(s) query wrapper with/without clause and parameters from slave.
     *
     * @param  array  $columns
     * @param  mixed $clause
     * @param  mixed $params
     * @param  string $type
     * @return mixed
     */
    public function selectSlave(array $columns, $clause = null, $params = null, $type = 'assoc')
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
     * @param  mixed $sql
     * @param  mixed $clause
     * @param  mixed $params
     * @param  mixed $type
     * @param  mixed $slave
     * @return mixed
     */
    private function getSelectWhereClause($sql, $clause, $params, $type, $slave)
    {
        if (!empty($clause)) {
            $clause = trim(preg_replace('/[\s]+/', ' ', $clause));

            $limitPosition = stripos($clause, 'limit');
            $orderByPosition = stripos($clause, 'order by');

            if (($orderByPosition === false && $limitPosition === false) ||
                ($orderByPosition === false && $limitPosition > 0) ||
                ($orderByPosition !== false && $orderByPosition > 0)) {
                $sql .= sprintf(' where %s', $clause);
            } elseif ($orderByPosition !== false || $limitPosition !== false) {
                $sql .= sprintf(' %s', $clause);
            }
        }

        if (!empty($params) && !is_array($params)) {
            $params = (array)$params;
        }

        $query = $this->cmd($sql, $slave === true);

        if (is_array($params)) {
            $query = $query->batch($params);
        }

        return $query->fetchAll($type);
    }

    /**
     * Insert query wrapper with accepted columns key/value pair values.
     *
     * @param  array  $columns
     * @return boolean
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

        return $this->cmd($sql)->batch($values)->run();
    }

    /**
     * Delete all records query wrapper.
     *
     * @return boolean
     */
    public function deleteAll()
    {
        $sql = sprintf('delete from %s', $this->table);
        return $this->cmd($sql)->run();
    }

    /**
     * Delete query wrapper with clause and parameters.
     *
     * @param  mixed $clause
     * @param  mixed $params
     * @return boolean
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
     * @param  array  $columns
     * @return boolean
     */
    public function updateAll(array $columns)
    {
        $values = array_values($columns);
        $sql = sprintf(
            'update %s set %s',
            $this->table,
            implode(' = ?, ', array_keys($columns)) . ' = ?'
        );

        return $this->cmd($sql)->batch($values)->run();
    }

    /**
     * Update query wrapper with accepted columns key/pair values, clause and params.
     *
     * @param  array  $columns
     * @param  mixed $clause
     * @param  mixed $params
     * @return boolean
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

        return $this->cmd($sql)->batch($values)->run();
    }

    /**
     * Perform update/insert based on the existence of record and types of database.
     * Default primary key column refers to `id`
     *
     * @param  mixed $id
     * @param  array  $columns
     * @return boolean
     */
    public function upsert($id, array $columns)
    {
        // mysql/maria
        if ($this->getConnectionInstance()->getMasterDriver() == 'mysql') {
            $values = array_values($columns);
            $sql = sprintf(
                'replace into %s (%s) values (%s)',
                $this->table,
                implode(', ', array_keys($columns)),
                $this->getPlaceholders($values)
            );

            return $this->cmd($sql)->batch($values)->run();
        // others
        } else {
            $sql = sprintf('select count(*) as total from %s where %s = :id', $this->table, $this->pk);
            $total = $this->cmd($sql)->bind(':id', $id)->fetchColumn();

            if ($total > 0) {
                return $this->update($columns, sprintf('%s = ?', $this->pk), $id);
            } else {
                return $this->insert($columns);
            }
        }
    }
    
    /**
     * Return the filled placeholders based on the values.
     *
     * @param  array  $values
     * @return string
     */
    public function getPlaceholders(array $values)
    {
        return $this->app->fillRepeat('?', ', ', 0, count($values));
    }
}