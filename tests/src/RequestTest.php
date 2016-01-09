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
        $this->http->setPost();
        $this->assertTrue($this->request->isPost());
    }

    public function testIsPut()
    {
        $this->http->setPut();
        $this->assertTrue($this->request->isPut());
    }

    public function testIsDelete()
    {
        $this->http->setDelete();
        $this->assertTrue($this->request->isDelete());
    }

    public function testIsOptions()
    {
        $this->http->setOptions();
        $this->assertTrue($this->request->isOptions());
    }

    public function testIsPatch()
    {
        $this->http->setPatch();
        $this->assertTrue($this->request->isPatch());
    }

    public function testIsHead()
    {
        $this->http->setHead();
        $this->assertTrue($this->request->isHead());
    }

    public function testIsTrace()
    {
        $this->http->setTrace();
        $this->assertTrue($this->request->isTrace());
    }

    public function testIsConnect()
    {
        $this->http->setConnect();
        $this->assertTrue($this->request->isConnect());
    }

    public function testGetRequestMethodGetLowercase()
    {
        $this->http->setGet();
        $this->assertEquals('get', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodPostLowercase()
    {
        $this->http->setPost();
        $this->assertEquals('post', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodPutLowercase()
    {
        $this->http->setPut();
        $this->assertEquals('put', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodDeleteLowercase()
    {
        $this->http->setDelete();
        $this->assertEquals('delete', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodOptionsLowercase()
    {
        $this->http->setOptions();
        $this->assertEquals('options', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodPatchLowercase()
    {
        $this->http->setPatch();
        $this->assertEquals('patch', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodHeadLowercase()
    {
        $this->http->setHead();
        $this->assertEquals('head', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodTraceLowercase()
    {
        $this->http->setTrace();
        $this->assertEquals('trace', $this->request->getRequestMethod(true));
    }

    public function testGetRequestMethodConnectLowercase()
    {
        $this->http->setConnect();
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