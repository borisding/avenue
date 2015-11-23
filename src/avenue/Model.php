<?php
namespace Avenue;

use Avenue\Database\Model;

abstract class Model extends Model  
{
    /**
     * Model class constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}