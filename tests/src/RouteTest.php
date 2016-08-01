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

    private $callback;

    private $callbackWithRegexp;

    private $callbackWithResouce;

    public function setUp()
    {
        $this->app = new App(['defaultController' => 'default']);
        $this->http = new Http();
        $this->route = $this->app->route;

        $this->http->set('PATH_INFO', '/foo/test/123');
        $this->rule = '(/@controller(/@action(/@id)))';

        $this->callback = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123'
            ];
        };

        $this->callbackWithRegexp = function() {
            return [
                '@controller' => ':alnum',
                '@action' => ':alnum',
                '@id' => ':digit'
            ];
        };

        $this->callbackWithResouce = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123',
                '@resource' => true,
            ];
        };

    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitInvalidNumberofArgumentsException()
    {
        $this->route->init(['param1', 'param2', 'param3']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitInvalidCallableForSecondArgumentsException()
    {
        $this->route->init(['param1', 'param2']);
    }

    public function testRouteIsFulfilled()
    {
        $this->route->init([$this->rule, $this->callback]);
        $this->assertEquals(1, $this->route->isFulfilled());
    }

    public function testRouteIsNotFulfilled()
    {
        $this->http->set('PATH_INFO', '/foo/bar/123');
        $this->route->init([$this->rule, $this->callback]);
        $this->assertEquals(0, $this->route->isFulfilled());
    }

    public function testRouteDefaultController()
    {
        $this->http->set('PATH_INFO', '/');
        $route = $this->getMock('Avenue\Route', ['initController'], [$this->app]);
        $route->setParam('controller', '');
        $route->init([$this->rule, $this->callbackWithRegexp]);
        $this->assertEquals($this->app->getConfig('defaultController'), $route->getParams('controller'));
    }

    public function testRouteDefaultControllerAction()
    {
        $this->http->set('PATH_INFO', '/foo');
        $route = $this->getMock('Avenue\Route', ['initController'], [$this->app]);
        $route->setParam('action', '');
        $route->init([$this->rule, $this->callbackWithRegexp]);
        $this->assertEquals('index', $route->getParams('action'));
    }

    public function testGetSpecificParams()
    {
        $this->route->init([$this->rule, $this->callback]);
        $this->assertEquals('foo', $this->route->getParams('controller'));
    }

    public function testGetAllParams()
    {
        $this->route->init([$this->rule, $this->callback]);
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
        $this->route->init([$this->rule, $this->callback]);
        $this->assertEquals('App\Controllers\FooController', $this->route->getControllerNamespace());
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowLogicExceptionForInitController()
    {
        $this->http->set('PATH_INFO', '/hello/test/123');
        $callback = function() {
            return [
                '@controller' => 'hello',
                '@action' => 'test',
                '@id' => '123'
            ];
        };
        $this->route->init([$this->rule, $callback]);
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowLogicExceptionForNoBaseController()
    {
        $this->http->set('PATH_INFO', '/bar/index/123');
        $callback = function() {
            return [
                '@controller' => 'bar',
                '@action' => 'index',
                '@id' => '123'
            ];
        };
        $this->route->init([$this->rule, $callback]);
    }

    public function testResourceWithGetAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $this->route->init([$this->rule, $this->callbackWithResouce]);
        $this->assertEquals('get', $this->route->getParams('action'));
    }

    public function testResourceWithPostAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'POST');
        $this->http->setPost();
        $this->route->init([$this->rule, $this->callbackWithResouce]);
        $this->assertEquals('post', $this->route->getParams('action'));
    }

    public function testResourceWithPutAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'PUT');
        $this->http->setPut();
        $this->route->init([$this->rule, $this->callbackWithResouce]);
        $this->assertEquals('put', $this->route->getParams('action'));
    }

    public function testResourceWithDeleteAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'DELETE');
        $this->http->setDelete();
        $this->route->init([$this->rule, $this->callbackWithResouce]);
        $this->assertEquals('delete', $this->route->getParams('action'));
    }

    public function testResourceWithGetTargetedControllerAction()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $callbackWithTargetedController = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123',
                '@resource' => 'foo|bar',
            ];
        };
        $this->route->init([$this->rule, $callbackWithTargetedController]);
        $this->assertEquals('get', $this->route->getParams('action'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowInvalidArgumentForResourceController()
    {
        $this->http->set('HTTP_X_HTTP_METHOD_OVERRIDE', 'GET');
        $this->http->setGet();
        $callbackWithTargetedController = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123',
                '@resource' => 'foo||bar',
            ];
        };
        $this->route->init([$this->rule, $callbackWithTargetedController]);
        $this->assertEquals('get', $this->route->getParams('action'));
    }
}