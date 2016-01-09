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
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '32.32.32.32';
    }

    public function getDefault()
    {
        return $_SERVER;
    }

    public function set($name, $value)
    {
        $_SERVER[$name] = $value;
    }

    public function get($name)
    {
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return '';
    }
}