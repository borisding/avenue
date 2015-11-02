<?php
namespace Avenue;

use \Closure;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\Exception;
use Avenue\Traits\HelperTrait;
use Avenue\Interfaces\AppInterface;

final class App implements AppInterface
{
    use HelperTrait;
    
    /**
     * Request instance.
     * 
     * @var object
     */
    public $request;
    
    /**
     * Response instance.
     * 
     * @var object
     */
    public $response;
    
    /**
     * Route instance;
     * 
     * @var object
     */
    public $route;
    
    /**
     * Cache respective services.
     * 
     * @var array
     */
    static $services = [];
    
    /**
     * Cache respective class instances.
     * 
     * @var array
     */
    static $instances = [];
    
    /**
     * Cache respective settings.
     * 
     * @var array
     */
    static $settings = [];
    
    /**
     * App class constructor.
     */
	public function __construct()
	{
	    $this
	    ->setTimezone()
	    ->setErrorHandler()
	    ->addRegistry()
	    ->getInstances();
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::route()
	 */
	public function route()
	{
	    if (!$this->route->isFulfilled()) {
	        return $this->route->init(func_get_args());
	    }
	    
	    return true;
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::addService()
	 */
	public function addService($name, Closure $callback)
	{
	    static::$services[$name] = $callback;
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::resolve()
	 */
	public function resolve($name, $args = null)
	{
	    if (!array_key_exists($name, static::$services)) {
	        throw new \OutOfBoundsException('Service [' . $name . '] is not registered!');
	    }
	    
	    $resolver = static::$services[$name];
	    
	    return $resolver($args);
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::singleton()
	 */
	public function singleton($name, $args = null)
	{
	    if (!array_key_exists($name, static::$instances)) {
	        static::$instances[$name] = $this->resolve($name, $args);
	    }
	    
	    if (!is_object(static::$instances[$name])) {
	        throw new \InvalidArgumentException('Non-object returned for [' .$name. '] in singleton.');
	    }
	    
	    return static::$instances[$name];
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::config()
	 */
	public function config($key)
	{
	    if (empty(static::$settings)) {
	        static::$settings = require_once AVENUE_APP_DIR . '/config.php';
	    }
	    
	    if (!array_key_exists($key, static::$settings)) {
	        throw new \OutOfBoundsException('Invalid config! [' . $key. '] is not set.');
	    }
	    
	    return static::$settings[$key];
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::render()
	 */
	public function render()
	{
	    // TODO
	}
	
	/**
	 * Shortcut of resolving registered services.
	 * 
	 * @param mixed $method
	 * @param array $params
	 * @throws \Exception
	 */
	public function __call($method, array $params = [])
	{
	    if (array_key_exists($method, static::$services)) {
	        return $this->resolve($method);
	    }
	    
	    if (!method_exists($this, $method)) {
	        throw new \BadMethodCallException('[' . $method  . '] method does not exist in App class!');
	    }
	}
	
	/**
	 * Set the application default timezone.
	 */
	protected function setTimezone()
	{
	    date_default_timezone_set($this->config('timezone'));
	    return $this;
	}
	
	/**
	 * Set the error and exception handlers.
	 * Error messages are render via error service.
	 * 
	 * @throws \ErrorException
	 */
	protected function setErrorHandler()
	{
	    set_exception_handler(function(\Exception $exc) {
	        // create custom exception class instance
	        // by passing the native exception class instance
	        $exception = $this->resolve('exception', $exc);
	        
	        // passing the custom exception class instance
	        // into the error service
	        return $this->resolve('error', $exception);
	    });
	    
        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        return $this;
	}
	
	/**
	 * Add the respective application registries via service container.
	 */
	protected function addRegistry()
	{
	    $this->addService('request', function() {
	        return new Request($this);
	    });
	    
        $this->addService('response', function() {
            return new Response($this);
        });
        
        $this->addService('route', function() {
            return new Route($this);
        });
        
        $this->addService('exception', function($exc) {
            return new Exception($this, $exc);
        });
        
	    return $this;
	}
	
	/**
	 * Retrieve respective class instances via singleton method.
	 */
	protected function getInstances()
	{
	    $this->request = $this->singleton('request');
	    $this->response = $this->singleton('response');
	    $this->route = $this->singleton('route');
	}
}