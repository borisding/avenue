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
    const GET_METHOD = 'GET';
    
    /**
     * Http post method.
     * 
     * @var mixed
     */
    const POST_METHOD = 'POST';
    
    /**
     * Http put method.
     * 
     * @var mixed
     */
    const PUT_METHOD = 'PUT';
    
    /**
     * Http delete method.
     * 
     * @var mixed
     */
    const DELETE_METHOD = 'DELETE';
    
    /**
     * Http options method.
     *
     * @var mixed
     */
    const OPTIONS_METHOD = 'OPTIONS';
    
    /**
     * Http patch method.
     *
     * @var mixed
     */
    const PATCH_METHOD = 'PATCH';
    
    /**
     * Http head method.
     *
     * @var mixed
     */
    const HEAD_METHOD = 'HEAD';
    
    /**
     * Http trace method.
     *
     * @var mixed
     */
    const TRACE_METHOD = 'TRACE';
    
    /**
     * Http connect method.
     *
     * @var mixed
     */
    const CONNECT_METHOD = 'CONNECT';
    
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
        return $this->getRequestMethod() === static::GET_METHOD;    
    }
    
    /**
     * Return true if http request method is POST.
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->getRequestMethod() === static::POST_METHOD;
    }
    
    /**
     * Return true if http request method is PUT.
     *
     * @return boolean
     */
    public function isPut()
    {
        return $this->getRequestMethod() === static::PUT_METHOD;
    }
    
    /**
     * Return true if http request method is DELETE.
     *
     * @return boolean
     */
    public function isDelete()
    {
        return $this->getRequestMethod() === static::DELETE_METHOD;
    }
    
    /**
     * Return true if http request method is OPTIONS.
     *
     * @return boolean
     */
    public function isOptions()
    {
        return $this->getRequestMethod() === static::OPTIONS_METHOD;
    }
    
    /**
     * Return true if http request method is PATCH.
     *
     * @return boolean
     */
    public function isPatch()
    {
        return $this->getRequestMethod() === static::PATCH_METHOD;
    }
    
    /**
     * Return true if http request method is HEAD.
     *
     * @return boolean
     */
    public function isHead()
    {
        return $this->getRequestMethod() === static::HEAD_METHOD;
    }
    
    /**
     * Return true if http request method is TRACE.
     *
     * @return boolean
     */
    public function isTrace()
    {
        return $this->getRequestMethod() === static::TRACE_METHOD;
    }
    
    /**
     * Return true if http request method is CONNECT.
     *
     * @return boolean
     */
    public function isConnect()
    {
        return $this->getRequestMethod() === static::CONNECT_METHOD;
    }
    
    /**
     * Return http request method.
     * Check against 'X-HTTP-Method-Override' and '_method' as well.
     * If none was found, then using GET as default. 
     * If lower case is true, returned as lower case instead.
     * 
     * @param string $lowerCase
     * @return mixed
     */
    public function getRequestMethod($lowerCase = false)
    {
        if ($this->getHeader('X-HTTP-Method-Override')) {
            $requestMethod = $this->getHeader('X-HTTP-Method-Override');
        } elseif (isset($_POST['_method']) && !empty($_POST['_method'])) {
            $requestMethod = $_POST['_method'];
        } else {
            $requestMethod = $this->app->arrGet('REQUEST_METHOD', $_SERVER, static::GET_METHOD);
        }
        
        return ($lowerCase) ? strtolower($requestMethod) : $requestMethod;
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
     * @param mixed $key
     */
    public function getHeader($key)
    {
        return $this->app->arrGet($key, $this->getAllHeaders());
    }
    
    /**
     * Get all http request headers.
     */
    public function getAllHeaders()
    {
        if (!function_exists('getallheaders')) {
            return $this->getCustomAllHeaders();
        }
        
        return getallheaders();
    }
    
    /**
     * Get all http request headers via alternative, eg: nginx
     */
    public function getCustomAllHeaders()
    {
        $headers = [];
        
        if (!is_array($_SERVER)) {
            return $headers;
        }
        
        foreach ($_SERVER as $key => $value) {
            
            if (substr($key, 0, 5) == 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = ucwords(strtolower($key));
                $key = str_replace(' ', '-', $key);
                
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Get url path info.
     * 
     * @return mixed
     */
    public function getPathInfo()
    {
        return $this->app->arrGet('PATH_INFO', $_SERVER);
    }
    
    /**
     * Get query string parameters.
     *
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->app->arrGet('QUERY_STRING', $_SERVER);
    }
    
    /**
     * Get the host name.
     */
    public function getHost()
    {
        return $this->app->arrGet('HTTP_HOST', $_SERVER);
    }
    
    /**
     * Get current URL's scheme.
     * 
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }
    
    /**
     * Get the script name.
     */
    public function getScriptName()
    {
        return $this->app->arrGet('SCRIPT_NAME', $_SERVER);
    }
    
    /**
     * Get the current request URI.
     */
    public function getRequestUri()
    {
        return $this->app->arrGet('REQUEST_URI', $_SERVER);
    }
    
    /**
     * Get the user agent.
     */
    public function getUserAgent()
    {
        return $this->app->arrGet('HTTP_USER_AGENT', $_SERVER, 'Unknown');
    }
    
    /**
     * Return the base URL.
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        $scriptName = $this->getScriptName();
        $appDir = str_replace(basename($scriptName), '', $scriptName);
        
        return sprintf('%s://%s%s', $this->getScheme(), $this->getHost(), $appDir);
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
        
        $this->app->response->setStatus(302);
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