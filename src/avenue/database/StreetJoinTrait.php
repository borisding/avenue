<?php
namespace Avenue\Database;

trait StreetJoinTrait
{
    /**
     * Inner join statement.
     *
     * @see \Avenue\Database\StreetInterface::join()
     */
    public function join($model, $on, $type = 'inner')
    {
        // if model passed in as model class object
        // try to get with model table property
        if (is_object($model)) {
            $table = $model->table;
        } else {
            $table = $model;
        }
    
        // decide the join type
        // default is inner join
        switch ($type) {
            case 'left':
                $join = 'LEFT JOIN';
                break;
            case 'right':
                $join = 'RIGHT JOIN';
                break;
            default:
                $join = 'INNER JOIN';
                break;
        }
    
        $this->sql .= sprintf(' %s %s ON %s', $join, $table, $on);
        unset($table, $join);
    
        return $this;
    }
    
    /**
     * Inner join method.
     *
     * @see \Avenue\Database\StreetInterface::innerJoin()
     */
    public function innerJoin($model, $on)
    {
        return $this->join($model, $on, 'inner');
    }
    
    /**
     * Left join method.
     *
     * @see \Avenue\Database\StreetInterface::leftJoin()
     */
    public function leftJoin($model, $on)
    {
        return $this->join($model, $on, 'left');
    }
    
    /**
     * Right join method.
     *
     * @see \Avenue\Database\StreetInterface::rightJoin()
     */
    public function rightJoin($model, $on)
    {
        return $this->join($model, $on, 'right');
    }
    
    /**
     * Cross join method by passing the targeted model class object.
     *
     * @see \Avenue\Database\StreetInterface::crossJoin()
     */
    public function crossJoin($model)
    {
        $this->sql .= sprintf(' %s %s', 'CROSS JOIN', $model->table);
        return $this;
    }
    
    /**
     * Natural join method by passing the targeted model class object.
     *
     * @see \Avenue\Database\StreetInterface::naturalJoin()
     */
    public function naturalJoin($model)
    {
        $this->sql .= sprintf(' %s %s', 'NATURAL JOIN', $model->table);
        return $this;
    }
}