<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    // example for getting all user records
    public function getAll()
    {
        return $this->select()->from('user')->query()->fetchAll();
    }
}
