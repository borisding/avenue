<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\RouteInterface;

class Route implements RouteInterface
{
    /**
     * App instance.
     * 
     * @var object
     */
    protected $app;
    
    /**
     * Controller instance.
     * 
     * @var object
     */
    protected $instance;
    
    /**
     * The route uri.
     * 
     * @var mixed
     */
    protected $uri;
    
    /**
     * The route filters.
     * 
     * @var array
     */
    protected $filters = [];
    
    /**
     * The uri params.
     * 
     * @var array
     */
    protected $params = [];
    
    /**
     * Flag to indicate route is fulfullied.
     * 
     * @var boolean
     */
    protected $fulfill = false;
    
    /**
     * Current http path info.
     * 
     * @var mixed
     */
    protected $pathInfo;
    
    /**
     * @var Route's regular expression patterns to be matched.
     */
    protected $regex = [
        ':alnum' => '([a-zA-Z0-9-_]+)',
        ':alpha' => '([a-zA-Z]+)',
        ':digit' => '([0-9]+)'
    ];
    
    /**
     * Prefix of controller namespace.
     *
     * @var string
     */
    const NAMESPACE_PREFIX = 'App\Controllers';
    
    /**
     * Base controller namespace.
     *
     * @var string
     */
    const BASE_CONTROLLER = 'Avenue\Controller';
    
    /**
     * Suffix of controller.
     *
     * @var string
     */
    const CONTROLLER_SUFFIX = 'Controller';
    
    /**
     * Suffix of controller action.
     *
     * @var string
     */
    const ACTION_SUFFIX = 'Action';
    
    /**
     * Route class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * @see \Avenue\Interfaces\RouteInterface::init()
     */
    public function init(array $args)
    {
        if (count($args) !== 2) {
            throw new \Exception('Route method is expecting two arguments.');
        }
        
        list($this->uri, $this->filters) = $args;
        
        if ($this->fulfill = $this->matchRoute()) {
            $this->extractUriTokens();
            
            $this
            ->withController()
            ->invokeBefore()
            ->invokeAction()
            ->invokeAfter();
        }
    }
    
    /**
     * Match the route URI with regular expression.
     * Return true if the particular route is matched.
     */
    protected function matchRoute()
    {
        // replace with the regexp patterns
        $uriRegex = strtr(strtr($this->uri, $this->filters), $this->regex);
        
        $uriRegex = str_replace(')', ')?', $uriRegex);
        
        $this->pathInfo = $this->app->request->pathInfo();
        
        return preg_match('#^/?' . $uriRegex . '/?$#', $this->pathInfo);
    }
    
    /**
     * Extract URI values and match with the respective params.
     */
    protected function extractUriTokens()
    {
        $this->uri = str_replace(')', '', str_replace('(', '', $this->uri));
        
        $fs = '/';
        
        if (strpos($this->uri, $fs) !== false && strpos($this->pathInfo, $fs) !== false) {
            $arrUri = explode($fs, $this->uri);
            
            $arrPathInfo = explode($fs, $this->pathInfo);
            
            // iterate over and set respective values to token
            for ($i = 0, $len = count($arrUri); $i < $len; $i++) {
                
                if (!empty($arrPathInfo[$i]) && strpos($arrUri[$i], '@') !== false) {
                    $key = $arrUri[$i];
                    
                    $value = $arrPathInfo[$i];
                    
                    $this->setParams($key, $this->app->escape($value));
                }
            }
        }
    }
    
    /**
     * Compute the namespace controller class and instantiate when found.
     * 
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected function withController()
    {
        $controller = $this->app->arrGet('@controller', $this->getAllParams(), $this->app->config('defaultController'));
        
        $controller = ucfirst($controller . static::CONTROLLER_SUFFIX);
        
        $namespaceController = static::NAMESPACE_PREFIX . '\\' . $controller;
        
        // throw exception if no controller class found
        if (!class_exists($namespaceController)) {
            // TODO: add the http status code
            
            throw new \LogicException('Controller [' . $namespaceController . '] not found.');
        }
        
        // check if controller class has parent controller
        if (!$this->isExtendedFromBase($namespaceController)) {
            // TODO: add the http status code
            
            throw new \LogicException('Controller must be extending the base controller!');
        }
        
        $this->instance = new $namespaceController();
        
        return $this;
    }
    
    /**
     * Invoke controller targeted action method.
     * If not found the default action will be invoked instead.
     * 
     * @throws \InvalidArgumentException
     */
    protected function invokeAction()
    {
        if (is_object($this->instance)) {
            $action = $this->app->arrGet('@action', $this->getAllParams(), 'index');
            
            $action = $action . static::ACTION_SUFFIX;
            
            if (!method_exists($this->instance, $action)) {
                // TODO: add the http status code
                
                throw new \BadMethodCallException('Controller action method [' . $action. '] not found.');
            }
            
            call_user_func([$this->instance, $action]);
        }
        
        return $this;
    }
    
    /**
     * Invoke controller before action method.
     * This will be called before any action.
     */
    protected function invokeBefore()
    {
        if (is_object($this->instance)) {
            $action = 'before' . static::ACTION_SUFFIX;
            
            call_user_func([$this->instance, $action]);
        }
        
        return $this;
    }
    
    /**
     * Invoke controller after action method.
     * This will be called after any action.
     */
    protected function invokeAfter()
    {
        if (is_object($this->instance)) {
            $action = 'after' . static::ACTION_SUFFIX;
        
            call_user_func([$this->instance, $action]);
        }
        
        return $this;
    }
    
    /**
     * To check whether the targetd controller is extending the base controller.
     */
    protected function isExtendedFromBase($targetedClass)
    {
        $parents = class_parents($targetedClass);
    
        return isset($parents[static::BASE_CONTROLLER]);
    }
    
    /**
     * @see \Avenue\Interfaces\RouteInterface::setParams()
     */
    public function setParams($key, $value)
    {
        return $this->params[$key] = $value;
    }
    
    /**
     * @see \Avenue\Interfaces\RouteInterface::getParams()
     */
    public function getParams($key)
    {
        return $this->app->arrGet($key, $this->params, null);
    }
    
    /**
     * @see \Avenue\Interfaces\RouteInterface::getAllParams()
     */
    public function getAllParams()
    {
        return $this->params;
    }
    
    /**
     * @see \Avenue\Interfaces\RouteInterface::isFulfilled()
     */
    public function isFulfilled()
    {
        return $this->fulfill;
    }
}