<?php
namespace Avenue\Interfaces;

use \Closure;

interface AppInterface
{
    /**
     * Route method that inits the route mapping.
     */
    public function route();
    
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
     * @param mixed $args
     */
    public function resolve($name, $args = null);
    
    /**
     * Return the singleton via object service.
     * 
     * @param mixed $name
     * @param mixed $args
     */
    public function singleton($name, $args = null);
    
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