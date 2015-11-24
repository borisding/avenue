<?php
namespace Avenue;

use \Closure;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Logger;
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
        ->registry()
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
     * @see \Avenue\Interfaces\AppInterface::service()
     */
    public function service($name, Closure $callback)
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
            $this->response->setHttpStatus(404);
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
            
            $this->response->setHttpStatus(500);
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        return $this;
    }
    
    /**
     * Returning avenue version.
     */
    public function version()
    {
        return static::AVENUE_VERSION;
    }
    
    /**
     * Returning app instance.
     */
    public static function instance()
    {
        return static::$app;
    }
    
    /**
     * Add the respective application registries.
     */
    protected function registry()
    {
        $this->service('request', function() {
            return new Request(static::$app);
        });
        
        $this->service('response', function() {
            return new Response(static::$app);
        });
        
        $this->service('route', function() {
            return new Route(static::$app);
        });
        
        $this->service('view', function() {
            return new View(static::$app);
        });
        
        $this->service('logger', function() {
            $monolog = $this->singleton('monolog');
            return new Logger(static::$app, $monolog);
        });
        
        $this->service('exception', function($exc) {
            return new Exception(static::$app, $exc);
        });
        
        return $this;
    }
    
    /**
     * Retrieving config value based on the key.
     *
     * @param mixed $key
     * @throws \OutOfBoundsException
     */
    public function config($key)
    {
        if (empty(static::$config)) {
            static::$config = require_once AVENUE_APP_DIR . '/config.php';
        }
    
        if (!array_key_exists($key, static::$config)) {
            throw new \OutOfBoundsException('Invalid config! [' . $key. '] is not set.');
        }
    
        return static::$config[$key];
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
        $this->view = $this->singleton('view');
    }
}