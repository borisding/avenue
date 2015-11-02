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
    public function withGet()
    {
        return $this->withMethod() === static::HTTP_GET;    
    }
    
    /**
     * Return true if http request method is POST.
     *
     * @return boolean
     */
    public function withPost()
    {
        return $this->withMethod() === static::HTTP_POST;
    }
    
    /**
     * Return true if http request method is PUT.
     *
     * @return boolean
     */
    public function withPut()
    {
        return $this->withMethod() === static::HTTP_PUT;
    }
    
    /**
     * Return true if http request method is DELETE.
     *
     * @return boolean
     */
    public function withDelete()
    {
        return $this->withMethod() === static::HTTP_DELETE;
    }
    
    /**
     * Return true if http request method is OPTIONS.
     *
     * @return boolean
     */
    public function withOptions()
    {
        return $this->withMethod() === static::HTTP_OPTIONS;
    }
    
    /**
     * Return true if http request method is PATCH.
     *
     * @return boolean
     */
    public function withPatch()
    {
        return $this->withMethod() === static::HTTP_PATCH;
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
    public function withMethod($lowerCase = false)
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
    public function withAjax()
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
}