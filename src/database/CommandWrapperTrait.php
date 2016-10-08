<?php
namespace Avenue\Database;

trait CommandWrapperTrait
{
    /**
     * Select all query wrapper with PK's value(s) and parameters from master.
     *
     * @param mixed $ids
     * @param array $clause
     * @param string $type
     */
    public function selectAll($ids = null, array $clause = [], $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectAllClause(),
            $ids,
            $clause,
            $type,
            true
        );
    }

    /**
     * Select all query wrapper with PK's value(s) and parameters from slave.
     *
     * @param mixed $ids
     * @param array $clause
     * @param string $type
     */
    public function selectAllSlave($ids = null, array $clause = [], $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectAllClause(),
            $ids,
            $clause,
            $type,
            false
        );
    }

    /**
     * Select column(s) query wrapper with PK's value(s) and parameters from master.
     *
     * @param array $columns
     * @param mixed $ids
     * @param array $clause
     * @param string $type
     */
    public function select(array $columns, $ids = null, array $clause = [], $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectClause($columns),
            $ids,
            $clause,
            $type,
            true
        );
    }

    /**
     * Select column(s) query wrapper with PK's value(s) and parameters from slave.
     *
     * @param array $columns
     * @param mixed $ids
     * @param array $clause
     * @param string $type
     */
    public function selectSlave(array $columns, $ids = null, array $clause = [], $type = 'assoc')
    {
        return $this->getSelectWhereClause(
            $this->getSelectClause($columns),
            $ids,
            $clause,
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
     * Return select statement with accepted column(s).
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
    private function getSelectWhereClause($sql, $values, array $clause, $type, $master)
    {
        if (empty($values)) {
            $values = [];
        } else {
            $sql .= ' where ';

            if (!is_array($values)) {
                $values = (array)$values;
                $sql .= sprintf('%s = %s', $this->pk, '?');
            } else {
                $sql .= sprintf('%s in (%s)', $this->pk, $this->getPlaceholders($values));
            }
        }

        $sql = $this->getClauseExtension($sql, $clause);
        $values = $this->getClauseInputs($values, $clause);

        $query = $this->cmd($sql, $master === true);

        if (!empty($values) && is_array($values)) {
            $query = $query->batch($values);
        }

        return $query->fetchAll($type);
    }

    /**
     * Insert query wrapper with accepted params.
     *
     * @param array $params
     */
    public function insert(array $params)
    {
        $values = array_values($params);
        $sql = sprintf(
            'insert into %s (%s) values (%s)',
            $this->table,
            implode(', ', array_keys($params)),
            $this->getPlaceholders($values)
        );

        return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
    }

    /**
     * Delete query wrapper with accepted PK value(s).
     *
     * @param mixed $ids
     * @param array $clause
     */
    public function delete($ids, array $clause = [])
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('[ids] argument must not be empty!', 400);
        }

        $sql = sprintf('delete from %s where ', $this->table);

        if (!is_array($ids)) {
            $ids = (array)$ids;
            $sql .= sprintf('%s = %s', $this->pk, '?');
        } else {
            $sql .= sprintf('%s in (%s)', $this->pk, $this->getPlaceholders($ids));
        }

        $sql = $this->getClauseExtension($sql, $clause);
        $values = $this->getClauseInputs($ids, $clause);

         return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
    }

    /**
     * Update query wrapper with accepted params and PK value(s).
     *
     * @param array $params
     * @param mixed $ids
     * @param array $clause
     */
    public function update(array $params, $ids, array $clause = [])
    {
        if (empty($params) || empty($ids)) {
            throw new \InvalidArgumentException('[params] or [ids] argument must not be empty!', 400);
        }

        $values = array_values($params);
        $sql = sprintf(
            'update %s set %s where ',
            $this->table,
            implode(' = ?, ', array_keys($params)) . ' = ?'
        );

        if (!is_array($ids)) {
            array_push($values, $ids);
            $sql .= sprintf('%s = %s', $this->pk, '?');
        } else {
            $values = array_merge($values, $ids);
            $sql .= sprintf('%s in (%s)', $this->pk, $this->getPlaceholders($ids));
        }

        $sql = $this->getClauseExtension($sql, $clause);
        $values = $this->getClauseInputs($values, $clause);

        return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
    }

    /**
     * Return sql statement with the first key of clause values.
     *
     * @param mixed $sql
     * @param array $clause
     * @return mixed
     */
    private function getClauseExtension($sql, array $clause)
    {
        if (!empty($clause)) {
            $sql .= sprintf(' %s', key($clause));
        }

        return $sql;
    }

    /**
     * Return placeholder input value(s) as assigned to clause.
     *
     * @param array $values
     * @param array $clause
     * @return array
     */
    private function getClauseInputs(array $values, array $clause)
    {
        if (empty($clause)) {
            return $values;
        }

        $inputs = array_values($clause)[0];

        if (!empty($inputs)) {

            if (!is_array($inputs)) {
                array_push($values, $inputs);
            } else {
                $values = array_merge($values, $inputs);
            }
        }

        return $values;
    }

    /**
     * Return the filled placeholders based on the values.
     *
     * @param mixed $values
     */
    private function getPlaceholders($values)
    {
        return $this->app->fillRepeat('?', ', ', 0, count($values));
    }
}