<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    public function getUsers()
    {        
        $result = $this
        ->column(['id', 'first_name', 'last_name'])
        ->find()
        ->where('id', 222)
        ->getAll();
        
        return $result;
    }
}