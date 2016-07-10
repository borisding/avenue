<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    // example for getting all user records from 'users' table
    public function getAll()
    {
        $result = $this->all();
        return $result;
    }
}