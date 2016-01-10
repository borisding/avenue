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

    public function testIsAjax()
    {
        $this->assertTrue($this->request->isAjax());
    }

    public function testIsNotAjax()
    {
        $this->http->set('HTTP_X_REQUESTED_WITH', '');
        $this->assertFalse($this->request->isAjax());
    }

    public function testIsSecureWithHttps()
    {
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsSecureWithServerPort()
    {
        $this->http->set('HTTPS', 'off');
        $this->http->set('SERVER_PORT', 443);
        $this->assertTrue($this->request->isSecure());
    }

    public function testIsNotSecureWithoutHttps()
    {
        $this->http->set('HTTPS', 'off');
        $this->assertFalse($this->request->isSecure());
    }

    public function testIsNotSecureWithServerPort()
    {
        $this->http->set('HTTPS', 'off');
        $this->http->set('SERVER_PORT', '8888');
        $this->assertFalse($this->request->isSecure());
    }

    public function testGetAllHeaders()
    {
        $allHeaders = $this->request->getAllHeaders();
        $this->assertTrue(is_array($allHeaders));
    }

    public function testGetHeaderByKey()
    {
        $this->assertEquals('localhost', $this->request->getHeader('Host'));
    }

    public function testGetNotExistHeaderByKey()
    {
        $this->assertEquals('', $this->request->getHeader('Dummy'));
    }

    public function testGetPathInfo()
    {
        $this->assertEquals('/controller/action', $this->request->getPathInfo());
    }

    public function testGetQueryString()
    {
        $this->assertEquals('user=test', $this->request->getQueryString());
    }

    public function testGetHost()
    {
        $this->assertEquals('localhost', $this->request->getHost());
    }

    public function testGetHttpsScheme()
    {
        $this->assertEquals('https', $this->request->getScheme());
    }

    public function testGetHttpScheme()
    {
        $this->http->set('HTTPS', 'off');
        $this->assertEquals('http', $this->request->getScheme());
    }

    public function testGetScriptName()
    {
        $this->assertEquals('/index.php', $this->request->getScriptName());
    }

    public function testGetRequestUri()
    {
        $this->assertEquals('/', $this->request->getRequestUri());
    }

    public function testGetUserAgent()
    {
        $this->assertEquals('Avenue', $this->request->getUserAgent());
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('https://localhost/', $this->request->getBaseUrl());
    }

    public function testRedirectWithBaseUrl()
    {
        $request = $this->getMockBuilder('\Avenue\Request')
        ->disableOriginalConstructor()
        ->getMock();

        $request
        ->method('redirect')
        ->willReturn('http://localhost/');

        $this->assertEquals('http://localhost/', $request->redirect('/'));
    }

    public function testRedirectWithoutBaseUrl()
    {
        $request = $this->getMockBuilder('\Avenue\Request')
        ->disableOriginalConstructor()
        ->getMock();

        $request
        ->method('redirect')
        ->willReturn('admin/default/index');

        $this->assertEquals('admin/default/index', $request->redirect('admin/default/index', false));
    }

    public function testGetBody()
    {
        $request = $this->getMockBuilder('\Avenue\Request')
                   ->disableOriginalConstructor()
                   ->getMock();
        $request
        ->method('getBody')
        ->willReturn('foo');

        $this->assertEquals('foo', $request->getBody());
    }

    public function testGetDirectory()
    {
        $ro = new \ReflectionObject($this->app->route);
        $p = $ro->getProperty('params');
        $p->setAccessible(true);
        $p->setValue($this->app->route, ['@directory' => 'admin']);
        $this->assertEquals('admin', $this->request->getDirectory());
    }

    public function testGetController()
    {
        $ro = new \ReflectionObject($this->app->route);
        $p = $ro->getProperty('params');
        $p->setAccessible(true);
        $p->setValue($this->app->route, ['@controller' => 'default']);
        $this->assertEquals('default', $this->request->getController());
    }

    public function testGetAction()
    {
        $ro = new \ReflectionObject($this->app->route);
        $p = $ro->getProperty('params');
        $p->setAccessible(true);
        $p->setValue($this->app->route, ['@action' => 'index']);
        $this->assertEquals('index', $this->request->getAction());
    }
}