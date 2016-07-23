<?php
namespace Avenue\Interfaces;

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
     * @param \Closure $callback
     */
    public function container($name, \Closure $callback);

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

    /**
     * Get the particular config.
     *
     * @param mixed $key
     */
    public function getConfig($key);

    /**
     * Get the default list of app config.
     */
    public function getDefaultConfig();

    /**
     * Get application version.
     */
    public function getAppVersion();

    /**
     * Get http version.
     */
    public function getHttpVersion();

    /**
     * Get timezone.
     */
    public function getTimezone();

    /**
     * Set application timezone.
     */
    public function setTimezone();

    /**
     * Get application environment.
     */
    public function getEnvironment();

    /**
     * Get default controller.
     */
    public function getDefaultController();

    /**
     * Get the app instance.
     */
    public static function getInstance();
}