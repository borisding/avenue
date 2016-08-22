<?php
namespace Avenue\Tests;

use Avenue\App;

require_once 'mocks/Http.php';
require_once 'mocks/FooController.php';
require_once 'mocks/BarController.php';

class RouteTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $route;

    private $http;

    private $rule;

    private $routeParam;

    private $routeParamWithRegexp;

    private $routeParamWithResouce;

    public function setUp()
    {
        $this->app = new App(['defaultController' => 'default']);
        $this->http = new Http();
        $this->route = $this->app->route();

        $this->http->set('PATH_INFO', '/foo/test/123');
        $this->rule = '(/@controller(/@action(/@id)))';

        $this->routeParam = [
            '@controller' => 'foo',
            '@action' => 'test',
            '@id' => '123'
        ];

        $this->routeParamWithRegexp = [
            '@controller' => ':alnum',
            '@action' => ':alnum',
            '@id' => ':digit'
        ];

        $this->routeParamWithResouce = [
            '@controller' => 'foo',
            '@action' => 'test',
            '@id' => '123',
            '@resource' => true,
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitInvalidNumberofArgumentsException()
    {
        $this->route->dispatch(['param1', 'param2', 'param3']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitInvalidArrayForSecondArgumentsException()
    {
        $this->route->dispatch(['param1', 'param2']);
    }

    public function testRouteIsFulfilled()
    {
        $this->route->dispatch([$this->rule, $this->routeParam]);
        $this->assertEquals(1, $this->route->isFulfilled());
    }

    public function testRouteIsNotFulfilled()
    {
        $this->http->set('PATH_INFO', '/foo/bar/123');
        $this->route->dispatch([$this->rule, $this->routeParam]);
        $this->assertEquals(0, $this->route->isFulfilled());
    }

    public function testRouteDefaultController()
    {
        $this->http->set('PATH_INFO', '/');
        $route = $this->getMock('\Avenue\Route', ['initController'], [$this->app]);
        $route->setParam('controller', '');
        $route->dispatch([$this->rule, $this->routeParamWithRegexp]);
        $this->assertEquals($this->app->getConfig('defaultController'), $route->getParams('controller'));
    }

    public function testRouteDefaultControllerAction()
    {
        $this->http->set('PATH_INFO', '/foo');
        $route = $this->getMock('\Avenue\Route', ['initController'], [$this->app]);
        $route->setParam('action', '');
        $route->dispatch([$this->rule, $this->routeParamWithRegexp]);
        $this->assertEquals('index', $route->getParams('action'));
    }

    public function testGetSpecificParams()
    {
        $this->route->dispatch([$this->rule, $this->routeParam]);
        $this->assertEquals('foo', $this->route->getParams('controller'));
    }

    public function testGetAllParams()
    {
        $this->route->dispatch([$this->rule, $this->routeParam]);
        $this->assertTrue(count(array_keys($this->route->getParams())) > 0);
    }

    public function testSetParams()
    {
        $this->route->setParam('foo', 'bar');
        $this->assertEquals('bar', $this->route->getParams('foo'));
    }

    public function testGetControllerNamespace()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $this->route->dispatch([$this->rule, $this->routeParam]);
        $this->assertEquals('App\Controllers\FooController', $this->route->getControllerNamespace());
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowLogicExceptionForInitController()
    {
        $this->http->set('PATH_INFO', '/hello/test/123');
        $routeParam = [
            '@controller' => 'hello',
            '@action' => 'test',
            '@id' => '123'
        ];
        $this->route->dispatch([$this->rule, $routeParam]);
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowLogicExceptionForNoBaseController()
    {
        $this->http->set('PATH_INFO', '/bar/index/123');
        $routeParam = [
            '@controller' => 'bar',
            '@action' => 'index',
            '@id' => '123'
        ];
        $this->route->dispatch([$this->rule, $routeParam]);
    }

    public function testResourceWithGetAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $this->route->dispatch([$this->rule, $this->routeParamWithResouce]);
        $this->assertEquals('get', $this->route->getParams('action'));
    }

    public function testResourceWithPostAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'POST');
        $this->http->setPost();
        $this->route->dispatch([$this->rule, $this->routeParamWithResouce]);
        $this->assertEquals('post', $this->route->getParams('action'));
    }

    public function testResourceWithPutAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'PUT');
        $this->http->setPut();
        $this->route->dispatch([$this->rule, $this->routeParamWithResouce]);
        $this->assertEquals('put', $this->route->getParams('action'));
    }

    public function testResourceWithDeleteAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'DELETE');
        $this->http->setDelete();
        $this->route->dispatch([$this->rule, $this->routeParamWithResouce]);
        $this->assertEquals('delete', $this->route->getParams('action'));
    }

    public function testResourceWithGetTargetedControllerAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $routeParamWithTargetedController = [
            '@controller' => 'foo',
            '@action' => 'test',
            '@id' => '123',
            '@resource' => 'foo|bar',
        ];
        $this->route->dispatch([$this->rule, $routeParamWithTargetedController]);
        $this->assertEquals('get', $this->route->getParams('action'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowInvalidArgumentForResourceController()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $routeParamWithTargetedController = [
            '@controller' => 'foo',
            '@action' => 'test',
            '@id' => '123',
            '@resource' => 'foo||bar',
        ];
        $this->route->dispatch([$this->rule, $routeParamWithTargetedController]);
        $this->assertEquals('get', $this->route->getParams('action'));
    }

    public function testMatchRouteSuccessForLowerUpperRoutePatterns()
    {
        $this->http->set('PATH_INFO', '/foo/TEST/123');
        $routeParam = [
            '@controller' => ':lower',
            '@action' => ':upper',
            '@id' => '123'
        ];
        $this->route->dispatch([$this->rule, $routeParam]);
        $this->assertEquals(1, $this->route->isFulfilled());
    }

    public function testMatchRouteFailedForLowerUpperRoutePatterns()
    {
        $this->http->set('PATH_INFO', '/FOO/test/123');
        $routeParam = [
            '@controller' => ':lower',
            '@action' => ':upper',
            '@id' => '123'
        ];
        $this->route->dispatch([$this->rule, $routeParam]);
        $this->assertEquals(0, $this->route->isFulfilled());
    }

    public function testMatchRouteSuccessForLowerUpperNumRoutePatterns()
    {
        $this->http->set('PATH_INFO', '/foo/TEST/abc123');
        $routeParam = [
            '@controller' => ':alnum',
            '@action' => ':uppernum',
            '@id' => ':lowernum'
        ];
        $this->route->dispatch([$this->rule, $routeParam]);
        $this->assertEquals(1, $this->route->isFulfilled());
    }

    public function testMatchRouteFailedForLowerUpperNumRoutePatterns()
    {
        $this->http->set('PATH_INFO', '/foo/TEST/abc123');
        $routeParam = [
            '@controller' => ':alnum',
            '@action' => ':lowernum',
            '@id' => 'uppernum'
        ];
        $this->route->dispatch([$this->rule, $routeParam]);
        $this->assertEquals(0, $this->route->isFulfilled());
    }
}
