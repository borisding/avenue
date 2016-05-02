<?php
namespace Avenue\Database;

trait StreetRelationTrait
{
    /**
     * One to one relationship.
     *
     * @param mixed $model
     * @param array $columns
     */
    public function hasOne($model, array $columns = [])
    {
        $on = $this->getOnCondition($model);

        return $this
        ->find($columns)
        ->innerJoin($model, $on);
    }

    /**
     * One to many relationship.
     *
     * @param mixed $model
     * @param array $columns
     */
    public function hasMany($model, array $columns = [])
    {
        $on = $this->getOnCondition($model);

        return $this
        ->find($columns)
        ->leftJoin($model, $on);
    }

    /**
     * Belongs to relationship.
     *
     * @param mixed $model
     * @param array $columns
     */
    public function belongsTo($model, array $columns = [])
    {
        $on = $this->getInverseOnCondition($model);

        return $this
        ->find($columns)
        ->innerJoin($model, $on);
    }

    /**
     * Shortcut of many to many through junction table.
     * Junction table and columns should be defined in array key/value pairs.
     * Will auto populate based on two model class objects if none is defined.
     *
     * @param mixed $model
     * @param array $junctionInfo
     * @param array $columns
     */
    public function hasManyThrough($model, array $junctionInfo = [], array $columns = [])
    {
        $defaultJunctionInfo = [
            'junction' => '',
            'firstId' => '',
            'secondId' => ''
        ];

        extract(array_merge($defaultJunctionInfo, $junctionInfo));

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
        ->find($columns)
        ->throughJoin($model, $junction, $firstId, $secondId);
    }

    /**
     * Get the on condition based on the current and targeted model.
     *
     * @param mixed $model
     */
    protected function getOnCondition($model)
    {
        $on  = $this->table . '.' . $this->getPk();
        $on .= ' = ';
        $on .= $model->table . '.' . $model->getFk();

        return $on;
    }

    /**
     * Get the inverse on condition based on the current and targeted model.
     *
     * @param mixed $model
     */
    protected function getInverseOnCondition($model)
    {
        $on  = $this->table . '.' . $this->getFk();
        $on .= ' = ';
        $on .= $model->table . '.' . $model->getPk();

        return $on;
    }
}