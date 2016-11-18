<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    // example for getting all user records from 'user' table
    public function getAll()
    {
        return $this->cmd(sprintf('select * from %s', $this->table))->fetchAll();
    }
}