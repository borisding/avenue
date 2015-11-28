<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    public function getAll()
    {
        $query = $this->cmd('select * from {user}');
        $result = $query->fetchAll();
        
        return $result;
    }
}