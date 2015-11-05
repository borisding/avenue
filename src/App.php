<?php
namespace Avenue;

use \Closure;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Exception;
use Avenue\Traits\HelperTrait;
use Avenue\Interfaces\AppInterface;

final class App implements AppInterface
{
    use HelperTrait;
    
    /**
     * Avenue version.
     * 
     * @var string
     */
    const AVENUE_VERSION = '1.0';
    
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
     * Cache respective registries.
     * 
     * @var array
     */
    static $registries = [];
    
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
     * App constructor
     */
    public function __construct()
    {
        $this
        ->setTimezone()
        ->setErrorHandler()
        ->addRegistry()
        ->factory();
    }
    
    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::container()
     */
    public function container($name, Closure $callback)
    {
        static::$registries[$name] = $callback;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::resolve()
     */
    public function resolve($name, $args = null)
    {
        if (!array_key_exists($name, static::$registries)) {
            throw new \OutOfBoundsException('Service [' . $name . '] is not registered!');
        }
        
        $resolver = static::$registries[$name];
        
        return $resolver($args);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::singleton()
     */
    public function singleton($name, $args = null)
    {
        if (!array_key_exists($name, static::$instances)) {
            static::$instances[$name] = $this->resolve($name, $args);
        }
        
        if (!is_object(static::$instances[$name])) {
            return null;
        }
        
        return static::$instances[$name];
    }
    
    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::render()
     */
    public function render()
    {
        // throw page not found exception, if any
        if (!$this->route->isFulfilled()) {
            $this->response->setHttpStatus(404);
            throw new \Exception('Page not found!');
        }
        
        $this->response->render();
    }
    
    /**
     * Shortcut of resolving registered registries.
     * 
     * @param mixed $method
     * @param array $params
     * @throws \Exception
     */
    public function __call($method, array $params = [])
    {
        if (array_key_exists($method, static::$registries)) {
            return $this->resolve($method);
        }
        
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException('[' . $method  . '] method does not exist in App class!');
        }
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
        $this->container('request', function() {
            return new Request($this);
        });
        
        $this->container('response', function() {
            return new Response($this);
        });
        
        $this->container('route', function() {
            return new Route($this);
        });
        
        $this->container('view', function() {
            return new View($this);
        });
        
        $this->container('exception', function($exc) {
            return new Exception($this, $exc);
        });
        
        return $this;
    }
    
    /**
     * Returning avenue version.
     */
    public function getVersion()
    {
        return static::AVENUE_VERSION;
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
     * Retrieve respective class instances via singleton method.
     */
    protected function factory()
    {
        $this->request = $this->singleton('request');
        $this->response = $this->singleton('response');
        $this->route = $this->singleton('route');
    }
}