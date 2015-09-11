<?php
namespace Avenue;

use \Closure;
use Avenue\Traits\HelperTrait;
use Avenue\Interfaces\AppInterface;

final class App implements AppInterface
{
    use HelperTrait;
    
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
	    $this->configTimezone()->configErrorHandlers();
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::route()
	 */
	public function route($segments = '', array $filters = [])
	{
	    // TODO
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::service()
	 */
	public function service($name, Closure $callback)
	{
	    static::$services[$name] = $callback;
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::resolve()
	 */
	public function resolve($name)
	{
	    if (!array_key_exists($name, static::$services)) {
	        throw new \OutOfBoundsException('Service [' . $name . '] is not registered!');
	    }
	    
	    $resolver = static::$services[$name];
	    
	    return $resolver();
	}
	
	/**
	 * @see \Avenue\Interfaces\AppInterface::singleton()
	 */
	public function singleton($name)
	{
	    if (!array_key_exists($name, static::$instances)) {
	        static::$instances[$name] = $this->resolve($name);
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
	        throw new \OutOfBoundsException('[' . $method  . '] method does not exist in App class!');
	    }
	}
	
	/**
	 * Configure the application default timezone.
	 */
	protected function configTimezone()
	{
	    date_default_timezone_set($this->config('timezone'));
	    
	    return $this;
	}
	
	/**
	 * Configure the error and exception handlers.
	 * Error messages are render via error service.
	 * 
	 * @throws \ErrorException
	 */
	protected function configErrorHandlers()
	{
	    set_exception_handler(function() {
	        $this->resolve('error');
	    });
	    
        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        return $this;
	}
}