<?php
namespace Avenue;

use \Closure;
use Avenue\App;
use Avenue\Helpers\HelperBundleTrait;

class View
{
    use HelperBundleTrait;
    
    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;
    
    /**
     * List of view helpers.
     * 
     * @var array
     */
    protected $helpers = [];
    
    /**
     * Parameters for magic methods.
     *
     * @var array
     */
    protected $params = [];
    
    /**
     * View class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Fetching the view file and getting the contents.
     *
     * @param mixed $name
     * @param array $params
     */
    public function fetch($name, array $params = [])
    {
        ob_start();
        // merge with direct variables assignment to object
        // the latter will overwrite the first
        extract(array_merge($this->params, $params));
        require $this->getViewFile($name);
        
        return ob_get_clean();
    }
    
    /**
     * Retrieve the view file.
     *
     * @param mixed $name
     * @throws \Exception
     * @return string
     */
    protected function getViewFile($name)
    {
        // assign with default .php extension
        // if there is no extension specified in file name
        if (empty(pathinfo($name, PATHINFO_EXTENSION))) {
            $name = $name . '.php';
        }
        
        $PATH_TO_VIEW_FILE = AVENUE_APP_DIR . '/views/' . $name;
        
        if (!file_exists($PATH_TO_VIEW_FILE)) {
            throw new \Exception('View [' . $PATH_TO_VIEW_FILE . '] not found!');
        }
        
        return $PATH_TO_VIEW_FILE;
    }
    
    /**
     * Register custom helper method.
     *
     * @param mixed $name
     * @param Closure $callback
     */
    public function register($name, Closure $callback)
    {
        if (array_key_exists($name, $this->helpers) || method_exists($this, $name)) {
            throw new \InvalidArgumentException('Helper name already registered!');
        }
        
        if (preg_match('/^[a-zA-Z0-9-_]+$/', $name) !== 1) {
            throw new \InvalidArgumentException('Invalid helper name! Alphanumeric only.');
        }
        
        $this->helpers[$name] = $callback;
    }
    
    /**
     * Magic call method for invoking added method.
     * 
     * @param mixed $name
     * @param array $params
     * @throws \LogicException
     * @return mixed
     */
    public function __call($name, array $params = [])
    {
        if (!array_key_exists($name, $this->helpers)) {
            throw new \LogicException('Calling invalid helper [' . $name . '].');
        }
        
        return call_user_func_array($this->helpers[$name], $params);
    }
    
    /**
     * Set magic method for view.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this->params[$key] = $value;
    }
    
    /**
     * Get magic method for view.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->app->arrGet($key, $this->params);
    }
}