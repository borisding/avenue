<?php
namespace Avenue;

use Avenue\App;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * Capsule object from database service.
     *
     * @var mixed
     */
    protected $db;

    /**
     * Model class constructor.
     * Resolve db service and return the Capsule manager class instance.
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        $this->db = $this->app->db();
    }
}