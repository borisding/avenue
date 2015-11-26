<?php
namespace Avenue;

use \Closure;

interface AppInterface
{
    /**
     * Route method that inits the route mapping.
     */
    public function addRoute();
    
    /**
     * Container method that registers the dependencies.
     * 
     * @param mixed $name
     * @param Closure $callback
     */
    public function addService($name, Closure $callback);
    
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
     * Rendering the application output.
     */
    public function render();
}