<?php
namespace Avenue;

use Avenue\App;

class Factory
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;

    /**
     * List of core classes.
     * Instances cannot be overwritten.
     * 
     * @var array
     */
    protected $core = [
        'request'   => 'Avenue\Request',
        'response'  => 'Avenue\Response',
        'route'     => 'Avenue\Route',
        'exception' => 'Avenue\Exception'
    ];

    /**
     * Factory class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Factory magic call method.
     * Check and throw error if core class object is being 'overwritten'.
     * 
     * @param  mixe $name
     * @param  array  $params
     */
    public function __call($name, array $params = [])
    {
        $instance = $this->app->singleton($name);

        if (isset($this->core[$name])) {

            // check if belongs to core class
            if (!$instance instanceof $this->core[$name]) {
                throw new \LogicException('Core class instance [' . $name . '] cannot be overwritten.');
            }
        }

        return $instance;
    }
}