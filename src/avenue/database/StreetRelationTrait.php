<?php
namespace Avenue\Database;

trait StreetRelationTrait
{
    public function hasOne($model, $on = null)
    {
        $on = $this->getOnCondition($model);
        
        return $this
        ->find()
        ->innerJoin($model, $on);
    }
    
    public function hasMany($model, $on = null)
    {
        $on = $this->getOnCondition($model, $on);
        
        return $this
        ->find()
        ->leftJoin($model, $on);
    }
    
    public function hasManyThrough($model, $junction = null, $on = null)
    {
        // if junction table is not defined
        // then concat current model table with targeted model table
        if (empty($junction)) {
            $junction = $this->table . '_' . $model->table;
        }
        
        $on = $this->getOnCondition($model, $on);
        
        $this->find();
        $this->sql .= sprintf(' INNER JOIN %s ', $junction);
        echo $this->sql;
    }
    
    /**
     * Get the on condition based on the current and targeted model.
     * 
     * @param mixed $model
     */
    protected function getOnCondition($model, $on)
    {
        if (empty($on)) {
            $on  = $this->table . '.' . $this->getPk();
            $on .= ' = ';
            $on .= $model->table . '.' . $this->getModelFk($model);
        }
        
        return $on;
    }
}