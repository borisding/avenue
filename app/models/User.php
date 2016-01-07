<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    public function getUsers()
    {
        // example: simplest query all
        $result = $this->findAll();
        return $result;
    }
}