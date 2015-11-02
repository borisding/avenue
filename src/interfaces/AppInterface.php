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
    public function addService($name, Closure $callback);
    
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