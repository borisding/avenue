<?php
namespace Avenue\Interfaces;

use \Closure;

interface AppInterface
{
    /**
     * Route method that invokes the method in Route class.
     * 
     * @param mixed $segment
     * @param array $filters
     */
    public function route($segment, array $filters = []);
    
    /**
     * Method that register the service by passing a function.
     * 
     * @param mixed $name
     * @param Closure $callback
     */
    public function service($name, Closure $callback);
    
    /**
     * Resolve the registered services.
     * 
     * @param mixed $name
     */
    public function resolve($name);
    
    /**
     * Return the singleton via object service.
     * 
     * @param mixed $name
     */
    public function singleton($name);
    
    /**
     * Retrieve the cofinguration value based on the key.
     * 
     * @param mixed $key
     */
    public function config($key);
    
    /**
     * Rendering the application output.
     */
    public function render();
}