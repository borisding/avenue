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
     * Get application environment.
     */
    public function getEnvironment();

    /**
     * Get default controller.
     */
    public function getDefaultController();

    /**
     * Get the app's secret.
     */
    public function getSecret();

    /**
     * Get the ID.
     */
    public function getId();

    /**
     * Get the app instance.
     */
    public static function getInstance();

    /**
     * Get current locale of application.
     */
    public function getLocale();
    
    /**
     * Set locale for application.
     *
     * @param mixed $locale
     * @param mixed $languageFile
     */
    public function setLocale($locale, $languageFile);

    /**
     * Translate source text.
     *
     * @param  mixed $source
     * @param  array  $values
     */
    public function t($source, array $values);
}
