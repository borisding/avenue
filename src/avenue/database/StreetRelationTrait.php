<?php
namespace Avenue\Database;

trait StreetRelationTrait
{
    /**
     * Shortcut of one to one relationship.
     * 
     * @param mixed $model
     * @param mixed $on
     */
    public function hasOne($model, $on = null)
    {
        $on = $this->getOnCondition($model);
        
        return $this
        ->find()
        ->innerJoin($model, $on);
    }
    
    /**
     * Shortcut of one to many relationship.
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