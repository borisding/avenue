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
     * Container method that registers the dependencies.
     * 
     * @param mixed $name
     * @param Closure $callback
     */
    public function service($name, Closure $callback);
    
    /**
     * Resolve the registered dependencies.
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