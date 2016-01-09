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

    public function testGetRequestMethodGetLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'GET');
        $this->assertEquals('get', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodPostLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'POST');
        $this->assertEquals('post', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodPutLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'PUT');
        $this->assertEquals('put', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodDeleteLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'DELETE');
        $this->assertEquals('delete', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodOptionsLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'OPTIONS');
        $this->assertEquals('options', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodPatchLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'PATCH');
        $this->assertEquals('patch', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodHeadLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'HEAD');
        $this->assertEquals('head', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodTraceLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'TRACE');
        $this->assertEquals('trace', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodConnectLowercase()
    {
        $this->http->set('REQUEST_METHOD', 'CONNECT');
        $this->assertEquals('connect', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodWithHeaderOverride()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'PUT');
        $this->assertTrue($this->request->isPut());
    }

    public function testGetRequestMethodWithPostMethod()
    {
        $_POST['_method'] = 'PUT';
        $this->assertTrue($this->request->isPut());
    }
}