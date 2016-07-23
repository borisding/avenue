<?php
namespace Avenue\Tests;

use Avenue\App;

require_once 'mocks/Http.php';
require_once 'mocks/FooController.php';

class RouteTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $route;

    private $http;

    public function setUp()
    {
        $this->app = new App(['defaultController' => 'default']);
        $this->http = new Http();
        $this->route = $this->app->route;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitInvalidNumberofArgumentsException()
    {
        $args = ['param1', 'param2', 'param3'];
        $this->route->init($args);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitInvalidCallableForSecondArgumentsException()
    {
        $args = ['param1', 'param2'];
        $this->route->init($args);
    }

    public function testRouteIsFulfilled()
    {
        $this->http->set('PATH_INFO', '/foo/test/123');
        $rule = '(/@controller(/@action(/@id)))';
        $callback = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123'
            ];
        };

        $this->route->init([$rule, $callback]);
        $this->assertEquals(1, $this->route->isFulfilled());
    }

    public function testRouteIsNotFulfilled()
    {
        $this->http->set('PATH_INFO', '/foo/bar/123');
        $rule = '(/@controller(/@action(/@id)))';
        $callback = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123'
            ];
        };

        $this->route->init([$rule, $callback]);
        $this->assertEquals(0, $this->route->isFulfilled());
    }

    public function testRouteDefaultController()
    {
        $this->http->set('PATH_INFO', '/');
        $rule = '(/@controller(/@action(/@id)))';
        $callback = function() {
            return [
                '@controller' => ':alnum',
                '@action' => ':alnum',
                '@id' => ':digit'
            ];
        };
        $route = $this->getMock('Avenue\Route', ['initController'], [$this->app]);
        $route->setParam('controller', '');
        $route->init([$rule, $callback]);
        $this->assertEquals($this->app->getConfig('defaultController'), $route->getParams('controller'));
    }

    public function testRouteDefaultControllerAction()
    {
        $this->http->set('PATH_INFO', '/foo');
        $rule = '(/@controller(/@action(/@id)))';
        $callback = function() {
            return [
                '@controller' => ':alnum',
                '@action' => ':alnum',
                '@id' => ':digit'
            ];
        };
        $route = $this->getMock('Avenue\Route', ['initController'], [$this->app]);
        $route->setParam('action', '');
        $route->init([$rule, $callback]);
        $this->assertEquals('index', $route->getParams('action'));
    }

    public function testGetParams()
    {
        $this->http->set('PATH_INFO', '/foo/test/123');
        $rule = '(/@controller(/@action(/@id)))';
        $callback = function() {
            return [
                '@controller' => 'foo',
                '@action' => 'test',
                '@id' => '123'
            ];
        };

        $args = [$rule, $callback];
        $this->route->init($args);
        $this->assertEquals('foo', $this->route->getParams('controller'));
    }

    public function testSetParams()
    {
        $this->route->setParam('foo', 'bar');
        $this->assertEquals('bar', $this->route->getParams('foo'));
    }
}