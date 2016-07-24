<?php
namespace Avenue;

use Avenue\App;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * Avenue class instance.
     *
     * @var \Avenue\App
     */
    protected $app;

    /**
     * Capsule object from database service.
     *
     * @var \Illuminate\Database\Capsule\Manager
     */
    protected $db;

    /**
     * Model class constructor.
     * Resolve db service and return the Capsule manager class instance.
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        $this->db = App::db();
    }
}