<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Tests\HttpMock;

require_once 'HttpMock.php';

class RequestTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $request;

    private $http;

    public function setUp()
    {
        $this->app = new App();
        $this->http = new HttpMock();
        $this->request = $this->app->request();
    }

    public function testIsGet()
    {
        $this->assertTrue($this->request->isGet());
    }

    public function testIsPost()
    {
        $this->http->set('REQUEST_METHOD', 'POST');
        $this->assertTrue($this->request->isPost());
    }

    public function testIsPut()
    {
        $this->http->set('REQUEST_METHOD', 'PUT');
        $this->assertTrue($this->request->isPut());
    }

    public function testIsDelete()
    {
        $this->http->set('REQUEST_METHOD', 'DELETE');
        $this->assertTrue($this->request->isDelete());
    }

    public function testIsOptions()
    {
        $this->http->set('REQUEST_METHOD', 'OPTIONS');
        $this->assertTrue($this->request->isOptions());
    }

    public function testIsPatch()
    {
        $this->http->set('REQUEST_METHOD', 'PATCH');
        $this->assertTrue($this->request->isPatch());
    }

    public function testIsHead()
    {
        $this->http->set('REQUEST_METHOD', 'HEAD');
        $this->assertTrue($this->request->isHead());
    }

    public function testIsTrace()
    {
        $this->http->set('REQUEST_METHOD', 'TRACE');
        $this->assertTrue($this->request->isTrace());
    }

    public function testIsConnect()
    {
        $this->http->set('REQUEST_METHOD', 'CONNECT');
        $this->assertTrue($this->request->isConnect());
    }
}