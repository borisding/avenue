<?php
namespace Avenue\Database;

use Avenue\Database\PdoAdapter;

class Model extends PdoAdapter
{
    /**
     * Base model class constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}