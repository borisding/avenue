<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\RequestInterface;

class Request implements RequestInterface
{
    /**
     * Avenue class instance.
     *
     * @var \Avenue\App
     */
    protected $app;

    /**
     * List of request methods.
     *
     * @var array
     */
    protected $methods = [
        'connect'   => 'CONNECT',
        'delete'    => 'DELETE',
        'get'       => 'GET',
        'head'      => 'HEAD',
        'options'   => 'OPTIONS',
        'patch'     => 'PATCH',
        'post'      => 'POST',
        'put'       => 'PUT',
        'trace'     => 'TRACE'
    ];

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
        return $this->getRequestMethod() === $this->methods['get'];
    }

    /**
     * Return true if http request method is POST.
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->getRequestMethod() === $this->methods['post'];
    }

    /**
     * Return true if http request method is PUT.
     *
     * @return boolean
     */
    public function isPut()
    {
        return $this->getRequestMethod() === $this->methods['put'];
    }

    /**
     * Return true if http request method is DELETE.
     *
     * @return boolean
     */
    public function isDelete()
    {
        return $this->getRequestMethod() === $this->methods['delete'];
    }

    /**
     * Return true if http request method is OPTIONS.
     *
     * @return boolean
     */
    public function isOptions()
    {
        return $this->getRequestMethod() === $this->methods['options'];
    }

    /**
     * Return true if http request method is PATCH.
     *
     * @return boolean
     */
    public function isPatch()
    {
        return $this->getRequestMethod() === $this->methods['patch'];
    }

    /**
     * Return true if http request method is HEAD.
     *
     * @return boolean
     */
    public function isHead()
    {
        return $this->getRequestMethod() === $this->methods['head'];
    }

    /**
     * Return true if http request method is TRACE.
     *
     * @return boolean
     */
    public function isTrace()
    {
        return $this->getRequestMethod() === $this->methods['trace'];
    }

    /**
     * Return true if http request method is CONNECT.
     *
     * @return boolean
     */
    public function isConnect()
    {
        return $this->getRequestMethod() === $this->methods['connect'];
    }

    /**
     * Return http request method.
     * Check against 'X-Http-Method-Override' and '_method' as well.
     * If none was found, then using GET as default.
     * If lower case is `true`, returned as lower case instead.
     *
     * @param string $lowerCase
     * @return mixed
     */
    public function getRequestMethod($lowerCase = false)
    {
        $requestMethod = $this->app->arrGet('REQUEST_METHOD', $_SERVER, $this->methods['get']);

        if (!empty($this->getHeader('X-Http-Method-Override'))) {
            $requestMethod = $this->getHeader('X-Http-Method-Override');
        } elseif (!empty($this->app->arrGet('_method', $_POST))) {
            $requestMethod = $this->app->arrGet('_method', $_POST);
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
     * Get particular header value based on the key.
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

        $getHeaderName = function($key) {
            $key = str_replace('_', ' ', $key);
            $key = ucwords(strtolower($key));
            $key = str_replace(' ', '-', $key);

            return $key;
        };

        if (is_array($_SERVER)) {

            foreach ($_SERVER as $key => $value) {

                if (substr($key, 0, 5) == 'HTTP_') {
                    $key = $getHeaderName(substr($key, 5));
                    $headers[$key] = $value;
                } elseif (substr($key, 0, 8) == 'CONTENT_') {
                    $key = $getHeaderName($key);
                    $headers[$key] = $value;
                }
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
     * Get and return the query string params in key/value pair.
     *
     * @return array
     */
    public function getParsedQueryString()
    {
        $queryString = $this->getQueryString();
        $queryParams = [];

        if (!empty($queryString)) {
            parse_str($queryString, $queryParams);
        }

        return $queryParams;
    }

    /**
     * Get the host name.
     */
    public function getHost()
    {
        return $this->app->arrGet('HTTP_HOST', $_SERVER);
    }

    /**
     * Get current URL scheme.
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

        $this->app->response()->withStatus(302);
        header('Location:' . $path);

        die();
    }

    /**
     * Get IP address.
     *
     * @return mixed
     */
    public function getIpAddress()
    {
        $ipAddress = $this->app->arrGet('REMOTE_ADDR', $_SERVER);

        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'] as $key) {

            if (!empty($this->app->arrGet($key, $_SERVER))) {
                $ipAddress = $this->app->arrGet($key, $_SERVER);
                break;
            }
        }

        return $ipAddress;
    }

    /**
     * Return the raw data via request body.
     * Not applicable for enctype="multipart/form-data".
     *
     * @return string
     */
    public function getBody()
    {
        $input = file_get_contents('php://input');
        return (!empty($input)) ? $input : '';
    }

    /**
     * Return requested prefix (directory).
     */
    public function getPrefix()
    {
        return $this->app->route()->getParams('prefix');
    }

    /**
     * Return requested controller.
     */
    public function getController()
    {
        return $this->app->route()->getParams('controller');
    }

    /**
     * Return the mapped controller namespace.
     */
    public function getNamespace()
    {
        return $this->app->route()->getParams('namespace');
    }

    /**
     * Return requested action.
     */
    public function getAction()
    {
        return $this->app->route()->getParams('action');
    }
}
