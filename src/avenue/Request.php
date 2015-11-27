<?php
namespace Avenue;

class Request
{
    /**
     * Avenue class instance.
     * 
     * @var mixed
     */
    protected $app;
    
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
    public function isGet()
    {
        return $this->getMethod() === static::HTTP_GET;    
    }
    
    /**
     * Return true if http request method is POST.
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->getMethod() === static::HTTP_POST;
    }
    
    /**
     * Return true if http request method is PUT.
     *
     * @return boolean
     */
    public function isPut()
    {
        return $this->getMethod() === static::HTTP_PUT;
    }
    
    /**
     * Return true if http request method is DELETE.
     *
     * @return boolean
     */
    public function isDelete()
    {
        return $this->getMethod() === static::HTTP_DELETE;
    }
    
    /**
     * Return true if http request method is OPTIONS.
     *
     * @return boolean
     */
    public function isOptions()
    {
        return $this->getMethod() === static::HTTP_OPTIONS;
    }
    
    /**
     * Return true if http request method is PATCH.
     *
     * @return boolean
     */
    public function isPatch()
    {
        return $this->getMethod() === static::HTTP_PATCH;
    }
    
    /**
     * Return http request method.
     * PUT, DELETE and OPTIONS methods can be checked via _method in POST.
     * If none was found, then using GET as default. 
     * If lower case is true, returned as lower case instead.
     * 
     * @param string $lowerCase
     * @return mixed
     */
    public function getMethod($lowerCase = false)
    {
        $arrHttpMethods = [static::HTTP_PUT, static::HTTP_DELETE, static::HTTP_OPTIONS];
        
        if (isset($_POST['_method']) && in_array($_POST['_method'], $arrHttpMethods)) {
            $httpMethod = $_POST['_method'];
        } else {
            $httpMethod = $this->app->arrGet('REQUEST_METHOD', $_SERVER, self::HTTP_GET);
        }
        
        if ($lowerCase) {
            return strtolower($httpMethod);
        } else {
            return $httpMethod;
        }
    }
    
    /**
     * Return true if request is called via Ajax.
     *
     * @return boolean
     */
    public function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }
        
        return false;
    }
    
    /**
     * To check if http is in secure mode.
     *
     * @return boolean
     */
    public function isSecure()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] == 443;
    }
    
    /**
     * Get particular header value based on the name.
     * 
     * @param mixed $name
     */
    public function getHeader($name)
    {
        return $this->app->arrGet($name, $this->getAllHeaders());
    }
    
    /**
     * Returning all http request headers.
     */
    public function getAllHeaders()
    {
        if (!function_exists('getallheaders')) {
            return $this->getAllNginxHeaders();
        } else {
            return getallheaders();
        }
    }
    
    /**
     * Returning nginx http request headers.
     * Credit: http://php.net/manual/en/function.getallheaders.php#84262
     */
    public function getAllNginxHeaders()
    {
        $headers = [];
        
        if (!is_array($_SERVER)) {
            return $headers;
        }
        
        foreach ($_SERVER as $name => $value) {
            
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = substr($name, 5);
                $key = str_replace('_', ' ', $key);
                $key = ucwords(strtolower($key));
                $key = str_replace(' ', '-', $key);
                
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Returning url's path info.
     * 
     * @return mixed
     */
    public function getPathInfo()
    {
        return $this->app->arrGet('PATH_INFO', $_SERVER);
    }
    
    /**
     * Returning query string parameters.
     *
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->app->arrGet('QUERY_STRING', $_SERVER);
    }
    
    /**
     * Return the host name.
     */
    public function getHost()
    {
        return $this->app->arrGet('HTTP_HOST', $_SERVER);
    }
    
    /**
     * Return current URL's scheme.
     * 
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }
    
    /**
     * Return the script name.
     */
    public function getScriptName()
    {
        return $this->app->arrGet('SCRIPT_NAME', $_SERVER);
    }
    
    /**
     * Return the current request URI.
     */
    public function getRequestUri()
    {
        return $this->app->arrGet('REQUEST_URI', $_SERVER);
    }
    
    /**
     * Return the base URL.
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        $entryScript = str_replace('/' . static::ENTRY_SCRIPT, '', $this->getScriptName());
        return $this->getScheme(). '://' . $this->getHost() . $entryScript;
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
            $path = $this->getBaseUrl() . $path;
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
    public function getBody()
    {
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            $input = '';
        }
        
        return $input;
    }
    
    /**
     * Return requested directory.
     */
    public function getDirectory()
    {
        return $this->app->route->getParams('@directory');
    }
    
    /**
     * Return requested controller.
     */
    public function getController()
    {
        return $this->app->route->getParams('@controller');
    }
    
    /**
     * Return requested action.
     */
    public function getAction()
    {
        return $this->app->route->getParams('@action');
    }
}