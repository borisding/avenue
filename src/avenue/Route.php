<?php
namespace Avenue;

use Avenue\App;

class Route
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
     * The route rule.
     * 
     * @var mixed
     */
    protected $rule;
    
    /**
     * The route filters.
     * 
     * @var array
     */
    protected $filters = [];
    
    /**
     * The route params.
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
        ':alnum' => '([a-zA-Z0-9-]+)',
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
     * Default action method.
     * 
     * @var string
     */
    const DEFAULT_ACTION = 'index';
    
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
     * Start with the route mapping by accepting the arguments from app route.
     *
     * @param array $args
     */
    public function init(array $args)
    {
        if (count($args) !== 2) {
            throw new \LogicException('Route method is expecting two arguments.');
        }
        
        if (!is_callable($args[1])) {
            throw new \LogicException('Second argument must be callable.');
        }
        
        $this->rule = $args[0];
        $this->filters = $args[1]();
        
        if ($this->fulfill = $this->matchRoute()) {
            $this
            ->setRouteParams()
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
        if (!is_array($this->filters)) {
            throw new \LogicException('Route callback should be returning array.');
        }
        
        // replace with the regexp patterns
        $ruleRegex = strtr(strtr($this->rule, $this->filters), $this->regex);
        $ruleRegex = str_replace(')', ')?', $ruleRegex);
        $this->pathInfo = $this->app->request->getPathInfo();
        
        return preg_match('#^/?' . $ruleRegex . '/?$#', $this->pathInfo);
    }
    
    /**
     * Set respective route params with actual value.
     */
    protected function setRouteParams()
    {
        $this->rule = str_replace(')', '', str_replace('(', '', $this->rule));
        $fs = '/';
        
        if (strpos($this->rule, $fs) !== false && strpos($this->pathInfo, $fs) !== false) {
            $arrUri = explode($fs, $this->rule);
            $arrPathInfo = explode($fs, $this->pathInfo);
            
            // iterate over and set respective values to token
            for ($i = 0, $len = count($arrUri); $i < $len; $i++) {
                
                if (!empty($arrPathInfo[$i]) && strpos($arrUri[$i], '@') !== false) {
                    $key = $arrUri[$i];
                    $value = $arrPathInfo[$i];
                    $this->setParam($key, $this->app->escape($value));
                }
            }
        }
        
        // set directory
        $this->setParam('@directory', $this->app->arrGet('@directory', $this->filters, ''));
        
        // set default controller if empty
        if (empty($this->getParams('@controller'))) {
            $this->setParam('@controller', $this->app->getConfig('defaultController'));
        }
        
        // set default action if empty
        if (empty($this->getParams('@action'))) {
            $this->setParam('@action', static::DEFAULT_ACTION);
        }
        
        return $this;
    }
    
    /**
     * Get the controller namespace and do the instantiation.
     * 
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected function withController()
    {
        $controllerClass = $this->buildNamespaceController();
        
        // throw exception if no controller class found
        if (!class_exists($controllerClass)) {
            $this->app->response->setStatus(404);
            throw new \LogicException('Controller [' . $controllerClass . '] not found.');
        }
        
        // check if controller class has parent controller
        if (!$this->isExtendedFromBase($controllerClass)) {
            $this->app->response->setStatus(400);
            throw new \LogicException('Controller must be extending the base controller!');
        }
        
        $this->instance = new $controllerClass($this->app);
        
        return $this;
    }
    
    /**
     * Build the controller namespace for the matched route.
     * If no controller is specified, the default controller will always be used.
     */
    protected function buildNamespaceController()
    {
        $fs = '/';
        $bs = '\\';
        $namespace = '';
        $directory = $this->app->escape($this->getParams('@directory'));
        $controller = $this->app->escape($this->getParams('@controller'));
        $controller = ucfirst($controller . static::CONTROLLER_SUFFIX);
        
        // check directory
        if (!empty($directory)) {
            if (strpos($directory, $fs) !== false) {
                $namespace .= implode($bs, array_map('ucfirst', explode($fs, $directory))) . $bs;
            } else {
                $namespace .= ucfirst($directory) . $bs;
            }
        }
        
        $namespace .= $controller;
        
        return static::NAMESPACE_PREFIX . $bs . $namespace;
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
            $action = $this->app->escape($this->getParams('@action'));
            $action .= static::ACTION_SUFFIX;
            
            if (!method_exists($this->instance, $action)) {
                $this->app->response->setStatus(404);
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
     * Set the particular URI token with a value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function setParam($key, $value)
    {
        return $this->params[$key] = $value;
    }
    
    /**
     * Get the particular token value based on the key.
     * Return all params if key is not provided.
     * 
     * @param mixed $key
     */
    public function getParams($key = null)
    {
        if (empty($key)) {
            return $this->params;
        } else {
            return $this->app->arrGet($key, $this->params, null);
        }
    }
    
    /**
     * Check if particular route is fulfilled.
     */
    public function isFulfilled()
    {
        return $this->fulfill;
    }
}