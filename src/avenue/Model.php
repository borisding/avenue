<?php
namespace Avenue;

use Avenue\Database\Model as BaseModel;

abstract class Model extends BaseModel  
{
    /**
     * Model class constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}