<?php
namespace Avenue\Tests;

class HttpMock
{
    public function __construct()
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '8888';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_USER_AGENT'] = 'Avenue';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['QUERY_STRING'] = '';
    }

    public function getDefault()
    {
        return $_SERVER;
    }

    public function set($name, $value)
    {
        $_SERVER[$name] = $value;
    }

    public function setGet()
    {
        $this->set('REQUEST_METHOD', 'GET');
    }

    public function setPost()
    {
        $this->set('REQUEST_METHOD', 'POST');
    }

    public function setPut()
    {
        $this->set('REQUEST_METHOD', 'PUT');
    }

    public function setDelete()
    {
        $this->set('REQUEST_METHOD', 'DELETE');
    }

    public function setPatch()
    {
        $this->set('REQUEST_METHOD', 'PATCH');
    }

    public function setOptions()
    {
        $this->set('REQUEST_METHOD', 'OPTIONS');
    }

    public function setHead()
    {
        $this->set('REQUEST_METHOD', 'HEAD');
    }

    public function setTrace()
    {
        $this->set('REQUEST_METHOD', 'TRACE');
    }

    public function setConnect()
    {
        $this->set('REQUEST_METHOD', 'CONNECT');
    }

    public function get($name)
    {
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return '';
    }
}