<?php
namespace App\Models;

use Avenue\Model;

class User extends Model
{
    public function getAll()
    {
        /**
         * Examples of using findAll() method
         */
        
        // example 1
        // SELECT * FROM user
        $result = $this->findAll();
        
        // example 2
        // SELECT * FROM user WHERE id = 100
        $result = $this->findAll(100);
        
        // example 3
        // SELECT * FROM user WHERE id IN (100, 101)
        $result = $this->findAll([100, 101]);
        
        // example 4
        // SELECT * FROM user WHERE id = 100
        $result = $this->findAll(function() {
            return 'WHERE id = 100';
        });
        
        // example 5
        // SELECT * FROM user WHERE id IN (100, 101) ORDER BY id DESC
        $result = $this->findAll([100, 101], function() {
            return 'ORDER BY id DESC';
        });
        
        return $result;
    }
}