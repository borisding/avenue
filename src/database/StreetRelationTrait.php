<?php
namespace Avenue\Database;

trait StreetRelationTrait
{
    /**
     * One to one relationship.
     *
     * @param mixed $model
     * @param mixed $on
     */
    public function hasOne($model, $on = null)
    {
        $on = $this->getOnCondition($model, $on);

        return $this
        ->find()
        ->innerJoin($model, $on);
    }

    /**
     * One to many relationship.
     *
     * @param mixed $model
     * @param mixed $on
     */
    public function hasMany($model, $on = null)
    {
        $on = $this->getOnCondition($model, $on);

        return $this
        ->find()
        ->leftJoin($model, $on);
    }

    /**
     * Belongs to relationship.
     *
     * @param mixed $model
     * @param mixed $on
     */
    public function belongsTo($model, $on = null)
    {
        $on = $this->getInverseOnCondition($model, $on);

        return $this
        ->find()
        ->innerJoin($model, $on);
    }

    /**
     * Shortcut of many to many through junction table.
     * Default junction, first ID and second ID will be defined respectively,
     * based on the table and id when it is not provied.
     *
     * @param mixed $model
     * @param mixed $junction
     * @param mixed $firstId
     * @param mixed $secondId
     */
    public function hasManyThrough($model, $junction = null, $firstId = null, $secondId = null)
    {
        // if empty, concat with first table and the latter with underscore
        if (empty($junction)) {
            $junction = $this->table . '_' . $model->table;
        }

        // if first id is empty, define default with current model table and id
        if (empty($firstId)) {
            $firstId = $this->table . '_' . 'id';
        }

        // if second id is empty, define default with model table and id
        if (empty($secondId)) {
            $secondId = $model->table . '_' . 'id';
        }

        return $this
        ->find()
        ->throughJoin($model, $junction, $firstId, $secondId);
    }

    /**
     * Get the on condition based on the current and targeted model.
     *
     * @param mixed $model
     * @param mixed $on
     */
    protected function getOnCondition($model, $on)
    {
        if (empty($on)) {
            $on  = $this->table . '.' . $this->getPk();
            $on .= ' = ';
            $on .= $model->table . '.' . $model->getFk();
        }

        return $on;
    }

    /**
     * Get the inverse on condition based on the current and targeted model.
     *
     * @param mixed $model
     * @param mixed $on
     */
    protected function getInverseOnCondition($model, $on)
    {
        if (empty($on)) {
            $on  = $this->table . '.' . $this->getFk();
            $on .= ' = ';
            $on .= $model->table . '.' . $model->getPk();
        }

        return $on;
    }
}