<?php
namespace Avenue;

use Avenue\Interfaces\RequestInterface;

class Request implements RequestInterface
{
       /**
     * Avenue class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * Magic method's parameters.
     * 
     * @var array
     */
    protected $params = [];
    
    /**
     * Http get method.
     * 
     * @var mixed
     */
    const HTTP_GET = 'GET';
    
    /**
     * Http post method.
     * 
     * @var mixed
     */
    const HTTP_POST = 'POST';
    
    /**
     * Http put method.
     * 
     * @var mixed
     */
    const HTTP_PUT = 'PUT';
    
    /**
     * Http delete method.
     * 
     * @var mixed
     */
    const HTTP_DELETE = 'DELETE';
    
    /**
     * Http options method.
     *
     * @var mixed
     */
    const HTTP_OPTIONS = 'OPTIONS';
    
    /**
     * Http patch method.
     *
     * @var mixed
     */
    const HTTP_PATCH = 'PATCH';
    
    /**
     * The entry script file name.
     * 
     * @var mixed
     */
    const ENTRY_SCRIPT = 'index.php';
    
    /**
     * Request class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Return true if http request method is GET.
     * 
     * @return boolean
     */
    public function httpGet()
    {
        return $this->httpMethod() === static::HTTP_GET;    
    }
    
    /**
     * Return true if http request method is POST.
     *
     * @return boolean
     */
    public function httpPost()
    {
        return $this->httpMethod() === static::HTTP_POST;
    }
    
    /**
     * Return true if http request method is PUT.
     *
     * @return boolean
     */
    public function httpPut()
    {
        return $this->httpMethod() === static::HTTP_PUT;
    }
    
    /**
     * Return true if http request method is DELETE.
     *
     * @return boolean
     */
    public function httpDelete()
    {
        return $this->httpMethod() === static::HTTP_DELETE;
    }
    
    /**
     * Return true if http request method is OPTIONS.
     *
     * @return boolean
     */
    public function httpOptions()
    {
        return $this->httpMethod() === static::HTTP_OPTIONS;
    }
    
    /**
     * Return true if http request method is PATCH.
     *
     * @return boolean
     */
    public function httpPatch()
    {
        return $this->httpMethod() === static::HTTP_PATCH;
    }
    
    /**
     * To check if http is in secure mode.
     *
     * @return boolean
     */
    public function httpSecure()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] == 443;
    }
    
    /**
     * Returning http request method.
     * PUT, DELETE and OPTIONS methods can be checked via _method in POST.
     * If none was found, then using GET as default. 
     * If lower case is true, returned as lower case instead.
     * 
     * @param string $lowerCase
     * @return mixed
     */
    public function httpMethod($lowerCase = false)
    {
        $arrHttpMethods = [static::HTTP_PUT, static::HTTP_DELETE, static::HTTP_OPTIONS];
        
        if (isset($_POST['_method']) && in_array($_POST['_method'], $arrHttpMethods)) {
            $httpMethod = $_POST['_method'];
        } else {
            $httpMethod = $this->app->arrGet('REQUEST_METHOD', $_SERVER, self::HTTP_GET);
        }
        
        if ($lowerCase) {
            return strtolower($httpMethod);
        }
        
        return $httpMethod;
    }
    
    /**
     * Return true if request is called via Ajax.
     *
     * @return boolean
     */
    public function viaAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }
        
        return false;
    }
    
    /**
     * Returning url's path info.
     * 
     * @return mixed
     */
    public function pathInfo()
    {
        return $this->app->arrGet('PATH_INFO', $_SERVER);
    }
        
    /**
     * Returning query string parameters.
     *
     * @return mixed
     */
    public function queryString()
    {
        return $this->app->arrGet('QUERY_STRING', $_SERVER);
    }
    
    /**
     * Return the host name.
     */
    public function host()
    {
        return $this->app->arrGet('HTTP_HOST', $_SERVER);
    }
    
    /**
     * Return current URL's scheme.
     * 
     * @return string
     */
    public function scheme()
    {
        return $this->httpSecure() ? 'https' : 'http';
    }
    
    /**
     * Return the script name.
     */
    public function scriptName()
    {
        return $this->app->arrGet('SCRIPT_NAME', $_SERVER);
    }
    
    /**
     * Return the current request URI.
     */
    public function requestUri()
    {
        return $this->app->arrGet('REQUEST_URI', $_SERVER);
    }
    
    /**
     * Return the base URL.
     * 
     * @return string
     */
    public function baseUrl()
    {
        return $this->scheme() . '://' . $this->host() . str_replace('/' . static::ENTRY_SCRIPT, '', $this->scriptName());
    }
    
    /**
     * Redirect to specified path.
     * Default is full path with base url appended.
     * 
     * @param mixed $path
     * @param string $baseUrl
     */
    public function redirect($path, $baseUrl = true)
    {
        if ($baseUrl) {
            $path = $this->baseUrl() . $path;
        }
        
        $this->app->response->setHttpStatus(302);
        
        header('Location:' . $path);
        
        die();
    }
    
    /**
     * Return the raw data via request body.
     * 
     * @return string
     */
    public function rawInput()
    {
        $rawInput = file_get_contents('php://input');
        
        if (empty($rawInput)) {
            $rawInput = '';
        }
        
        return $rawInput;
    }
    
    /**
     * Set magic method for request.
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
     * Get magic method for request.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->app->arrGet($key, $this->params);
    }
}