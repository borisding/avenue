<?php
namespace Avenue\Database;

trait CommandWrapperTrait
{
    /**
     * Select all query wrapper with PK's value(s) and parameters.
     * Default is reading from master.
     *
     * @param mixed $ids
     * @param string $master
     * @param string $type
     */
    public function selectAll($ids = null, $master = true, $type = 'assoc')
    {
        $sql = sprintf('select * from %s', $this->table);
        return $this->selectConditionBuilder($sql, $ids, $master, $type);
    }

    /**
     * Select column(s) query wrapper with PK's value(s) and parameters.
     * Default is reading from master.
     *
     * @param array $columns
     * @param mixed $ids
     * @param string $master
     * @param string $type
     */
    public function selectWith(array $columns, $ids = null, $master = true, $type = 'assoc')
    {
        $sql = sprintf('select %s from %s', implode(', ', $columns), $this->table);
        return $this->selectConditionBuilder($sql, $ids, $master, $type);
    }

    /**
     * Select statement's condition builder.
     *
     * @param mixed $sql
     * @param mixed $ids
     * @param mixed $master
     * @param mixed $type
     */
    private function selectConditionBuilder($sql, $ids, $master, $type)
    {
        if (!empty($ids)) {
            $sql .= ' where ';
        }

        if (!empty($ids) && !is_array($ids)) {
            $ids = [$ids];
            $sql .= sprintf('%s = %s', $this->pk, '?');
        } elseif ($this->app->arrIsIndex($ids)) {
            $sql .= sprintf('%s in (%s)', $this->pk, $this->getPlaceholders($ids));
        }

        $readMaster = $master === true;
        $query = $this->cmd($sql, $readMaster);

        if (is_array($ids)) {
            $query = $query->batch($ids);
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
     */
    public function delete($ids)
    {
        $sql = sprintf('delete from %s where ', $this->table);

        if (!is_array($ids)) {
            $ids = [$ids];
            $sql .= sprintf('%s = %s', $this->pk, '?');
        } elseif ($this->app->arrIsIndex($ids)) {
            $sql .= sprintf('%s in (%s)', $this->pk, $this->getPlaceholders($ids));
        }

         return $this
        ->cmd($sql)
        ->batch($ids)
        ->run();
    }

    /**
     * Update query wrapper with accepted params and PK value(s).
     *
     * @param array $params
     * @param mixed $ids
     */
    public function update(array $params, $ids)
    {
        $values = array_values($params);

        $sql = sprintf(
            'update %s set %s ',
            $this->table,
            implode(' = ?, ', array_keys($params)) . ' = ?'
        );

        if (!empty($ids)) {
            $sql .= ' where ';
        }

        if (!empty($ids) && !is_array($ids)) {
            array_push($values, $ids);
            $sql .= sprintf('%s = %s', $this->pk, '?');
        } elseif ($this->app->arrIsIndex($ids)) {
            $values = array_merge($values, $ids);
            $sql .= sprintf('%s in (%s)', $this->pk, $this->getPlaceholders($ids));
        }

        return $this
        ->cmd($sql)
        ->batch($values)
        ->run();
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