<?php
namespace Avenue\Database;

trait CommandWrapperTrait
{
    /**
     * Select all query wrapper with/without clause and parameters.
     *
     * @param  mixed $clause
     * @param  array $params
     * @return mixed
     */
    public function selectAll($clause = '', array $params = [])
    {
        return $this->getSelectWhereClause(
            sprintf('select * from %s', $this->table),
            $clause,
            $params
        );
    }

    /**
     * Select count column query wrapper with/without clause and parameters.
     *
     * @param  string $column
     * @param  string $clause
     * @param  array $params
     * @return mixed
     */
    public function selectCount($column = '*', $clause = '', array $params = [])
    {
        if (strpos($column, ':') !== false) {
            list($column, $alias) = explode(':', $column);
            $select = sprintf('select count(%s) as %s from %s', trim($column), trim($alias), $this->table);
        } else {
            $select = sprintf('select count(%s) from %s', trim($column), $this->table);
        }

        return $this->getSelectWhereClause(
            $select,
            $clause,
            $params
        );
    }

    /**
     * Select disticnt column(s) query wrapper with/without clause and parameters.
     *
     * @param  array  $columns
     * @param  string $clause
     * @param  array $params
     * @return mixed
     */
    public function selectDistinct(array $columns, $clause = '', array $params = [])
    {
        return $this->getSelectWhereClause(
            sprintf('select distinct %s from %s', implode(', ', $columns), $this->table),
            $clause,
            $params
        );
    }

    /**
     * Select column(s) query wrapper with/without clause and parameters.
     *
     * @param  array  $columns
     * @param  mixed $clause
     * @param  array $params
     * @return mixed
     */
    public function select(array $columns, $clause = '', array $params = [])
    {
        return $this->getSelectWhereClause(
            sprintf('select %s from %s', implode(', ', $columns), $this->table),
            $clause,
            $params
        );
    }

    /**
     * Select statement's with where clause builder.
     * Default select from slave, and divert to master instead if slave not applicable.
     *
     * @param  mixed $sql
     * @param  mixed $clause
     * @param  array $params
     * @return mixed
     */
    private function getSelectWhereClause($sql, $clause, $params)
    {
        if (!empty($clause)) {

            if (stripos($clause, 'where') === false) {
                $trimmedClause = trim(preg_replace('/[\s]+/', ' ', $clause));
                $keywords = ['limit', 'order by', 'group by', 'having'];
                $startWithKeyword = false;
                
                foreach ($keywords as $keyword) {
                    $position = stripos($trimmedClause, $keyword);

                    if ($position !== false && $position === 0) {
                        $sql .= sprintf(' %s', $clause);
                        $startWithKeyword = true;
                        break;
                    }
                }

                if (!$startWithKeyword) {
                    $sql .= sprintf(' where %s', $clause);
                }
            } else {
                $sql .= sprintf(' %s', trim($clause));
            }
        }

        $query = $this->cmd($sql, true);

        if (is_array($params)) {
            $query = $query->batch($params);
        }

        return $query;
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
     * @param  array $params
     * @return boolean
     */
    public function delete($clause, array $params = [])
    {
        $sql = sprintf('delete from %s where %s', $this->table, $clause);
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
     * @param  array $params
     * @return boolean
     */
    public function update(array $columns, $clause, array $params = [])
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
                return $this->update($columns, sprintf('%s = ?', $this->pk), [$id]);
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
