<?php
namespace Avenue;

use \Closure;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Exception;
use Avenue\AppInterface;
use Avenue\Helpers\HelperBundleTrait;

final class App implements AppInterface
{
    use HelperBundleTrait;
    
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
     * View instsance.
     * 
     * @var object
     */
    public $view;
    
    /**
     * Route instance;
     * 
     * @var object
     */
    public $route;
    
    /**
     * App instance.
     * 
     * @var object
     */
    protected static $app;
    
    /**
     * List of respective services.
     * 
     * @var array
     */
    protected static $services = [];
    
    /**
     * List of respective class instances.
     * 
     * @var array
     */
    protected static $instances = [];
    
    /**
     * List of respective configurations.
     * 
     * @var array
     */
    protected static $config = [];
    
    /**
     * App constructor
     */
    public function __construct()
    {
        if (empty(static::$app)) {
            static::$app = $this;
        }
        
        $this
        ->setTimezone()
        ->setErrorHandler()
        ->addRegistry()
        ->factory();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\AppInterface::addRoute()
     */
    public function addRoute()
    {
        if (!$this->route->isFulfilled()) {
            return $this->route->init(func_get_args());
        }
        
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\AppInterface::container()
     */
    public function container($name, Closure $callback)
    {
        static::$services[$name] = $callback;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::resolve()
     */
    public function resolve($name, $args = null)
    {
        if (!array_key_exists($name, static::$services)) {
            $this->response->setStatus(500);
            throw new \OutOfBoundsException('Service [' . $name . '] is not registered!');
        }
        
        $resolver = static::$services[$name];
        
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
     * @see \Avenue\Interfaces\AppInterface::render()
     */
    public function render()
    {
        // throw page not found exception, if any
        if (!$this->route->isFulfilled()) {
            $this->response->setStatus(404);
            throw new \Exception('Page not found!');
        }
        
        $this->response->render();
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
            // resolving exception service
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
            
            $this->response->setStatus(500);
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        return $this;
    }
    
    /**
     * Add the respective application registries.
     */
    protected function addRegistry()
    {
        $this->container('request', function() {
            return new Request(static::getInstance());
        });
        
        $this->container('response', function() {
            return new Response(static::getInstance());
        });
        
        $this->container('route', function() {
            return new Route(static::getInstance());
        });
        
        $this->container('view', function() {
            return new View(static::getInstance());
        });
        
        $this->container('exception', function($exc) {
            return new Exception(static::getInstance(), $exc);
        });
        
        return $this;
    }
    
    /**
     * Retrieving config value based on the key.
     * 
     * @param mixed $key
     */
    public function getConfig($key)
    {
        if (empty(static::$config)) {
            static::$config = require_once AVENUE_APP_DIR . '/config/app.php';
        }
        
        if (!array_key_exists($key, static::$config)) {
            static::$config[$key] = null;
        }
    
        return static::$config[$key];
    }
    
    /**
     * Returning avenue version.
     */
    public function getVersion()
    {
        return static::AVENUE_VERSION;
    }
    
    /**
     * Returning app instance.
     */
    public static function getInstance()
    {
        return static::$app;
    }
    
    /**
     * Set the application default timezone.
     */
    protected function setTimezone()
    {
        date_default_timezone_set($this->getConfig('timezone'));
        return $this;
    }
    
    /**
     * Retrieve respective class instances via singleton method.
     */
    protected function factory()
    {
        $this->request = $this->request();
        $this->response = $this->response();
        $this->route = $this->route();
        $this->view = $this->view();
    }
    
    /**
     * App call magic method.
     * Shortcut of creating instance via singleton.
     * 
     * @param mixed $name
     * @param array $params
     * @return NULL|NULL
     */
    public function __call($name, array $params = [])
    {
        if (array_key_exists($name, static::$services)) {
            return $this->singleton($name);
        }
        
        if (!method_exists($this, $name)) {
            throw new \Exception('Calling invalid App class method [' . $name . ']');
        }
    }
}