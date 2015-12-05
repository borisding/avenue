<?php
namespace Avenue\Database;

trait StreetRelationTrait
{
    /**
     * One to one relationship.
     * 
     * @param mixed $model
     * @return \Avenue\Database\StreetRelationTrait
     */
    public function hasOne($model)
    {
        $this->sql = sprintf(
            '%s INNER JOIN %s ON %s = %s',
            $this->find()->getSql(),
            $model->table,
            $this->table . '.' . $this->pk,
            $model->table. '.' . $this->getModelFk($model)
        );
        
        return $this;
    }
}