<?php
namespace Avenue\Interfaces;

interface RequestInterface
{
    /**
     * Check if http request is GET method.
     */
    public function isGet();

    /**
     * Check if http request is POST method.
     */
    public function isPost();

    /**
     * Check if http request is PUT method.
     */
    public function isPut();

    /**
     * Check if http request is DELETE method.
     */
    public function isDelete();

    /**
     * Check if http request is OPTIONS method.
     */
    public function isOptions();

    /**
     * Check if http request is PATCH method.
     */
    public function isPatch();

    /**
     * Check if http request is HEAD method.
     */
    public function isHead();

    /**
     * Check if http request is TRACE method.
     */
    public function isTrace();

    /**
     * Check if http request is CONNECT method.
     */
    public function isConnect();

    /**
     * Get the current http request method.
     * Returned as lowercase if the parameter is true.
     *
     * @param boolean $lowerCase
     */
    public function getRequestMethod($lowerCase = false);

    /**
     * Check if request is made via Ajax.
     */
    public function isAjax();

    /**
     * Check if secure http.
     */
    public function isSecure();

    /**
     * Get particular header value based on the key.
     *
     * @param mixed $key
     */
    public function getHeader($key);

    /**
     * Get all headers of the http request.
     */
    public function getAllHeaders();

    /**
     * Alternative for getting all headers of http request via custom handling.
     * Useful for say, nginx web server.
     */
    public function getCustomAllHeaders();

    /**
     * Get the URL path info.
     */
    public function getPathInfo();

    /**
     * Get the query string parameters.
     */
    public function getQueryString();

    /**
     * Get the host name.
     */
    public function getHost();

    /**
     * Get the current URL's scheme.
     */
    public function getScheme();

    /**
     * Get the page script name.
     */
    public function getScriptName();

    /**
     * Get the current request URI.
     */
    public function getRequestUri();

    /**
     * Get the current user agent.
     */
    public function getUserAgent();

    /**
     * Get the base URL of request.
     */
    public function getBaseUrl();

    /**
     * Page redirection with path provided.
     * Base URL is included if second parameter is true.
     *
     * @param mixed $path
     * @param string $baseUrl
     */
    public function redirect($path, $baseUrl = true);

    /**
     * Get the raw data of request body.
     */
    public function getBody();

    /**
     * Alias method for getting namespace prefix (directory) name that provided in route for request.
     */
    public function getPrefix();

    /**
     * Alias method for getting controller name that provided in route for request.
     */
    public function getController();

    /**
     * Alias method for getting action name that provided in route for request.
     */
    public function getAction();
}