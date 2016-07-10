<?php
namespace Avenue\Interfaces;

use Closure;

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
    public function container($name, Closure $callback);

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
     * Running application by rendering the application output.
     */
    public function run();
}