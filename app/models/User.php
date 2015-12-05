<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    public function getUsers()
    {        
        $result = $this
        ->with(['id', 'first_name', 'last_name', 'age'])
        ->find()
        ->groupBy(['last_name', 'first_name'])
        ->orderBy(['age DESC', 'last_name DESC'])
        ->getAll();
        
        return $result;
    }
}