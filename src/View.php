<?php
namespace Avenue;

use Avenue\App;

class View
{
    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;
    
    /**
     * Parameters for magic methods.
     *
     * @var array
     */
    protected $params = [];
    
    /**
     * Default view file extension.
     *
     * @var mixed
     */
    const EXT = '.php';
    
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
        $mergedParams = array_merge($this->params, $params);
        extract($mergedParams);
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
            $name = $name . static::EXT;
        }
        
        $PATH_TO_VIEW_FILE = AVENUE_APP_DIR . '/views/' . $name;
        
        if (!file_exists($PATH_TO_VIEW_FILE)) {
            throw new \Exception('View [' . $PATH_TO_VIEW_FILE . '] not found!');
        }
        
        return $PATH_TO_VIEW_FILE;
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