<?php
namespace Avenue\Tests;

use Avenue\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App();
    }

    public function testGetInstance()
    {
        $this->assertEquals($this->app, App::getInstance(), 'Both app instances are the same.');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRouteInvalidNumberOfParameters()
    {
        $this->app->addRoute('param1', 'param2', function() {});
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRouteSecondParamIsNotCallable()
    {
        $this->app->addRoute('param1', 'param2');
    }

    public function testAddRouteSecondParamIsCallable()
    {
        $this->app->addRoute('param1', function() {
            return [];
        });

        // if route has passed, expects fullfiled is boolean
        $fulfilled = $this->app->route->isFulfilled();
        $this->assertEquals($fulfilled, false, 'Fulfilled should return boolean.');
    }

    public function testContainer()
    {
        $this->app->container('calculation', function() {
            $result = 1 + 1;
            return $result;
        });

        $this->assertEquals($this->app->resolve('calculation'), 2, 'Calculation result from container is equal 2.');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testContainerInvalidNameException()
    {
        $this->app->container('my.calculation', function() {
            $result = 1 + 1;
            return $result;
        });

        $this->app->resolve('my.calculation');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testResolveOutOfBoundException()
    {
        $this->app->resolve('serviceDoesNotExist');
    }

    public function testSingletonReturnsNull()
    {
        $this->app->container('classObjectNotExist', function() {
            return 'I am just a string.';
        });

        $object = $this->app->singleton('classObjectNotExist');
        $this->assertNull($object, 'Class object is null.');
    }

    public function testSingletonReturnsClassInstance()
    {
        $this->app->container('request', function() {
            return new \Avenue\Request($this->app);
        });

        $instance = $this->app->singleton('request');
        $this->assertTrue($instance instanceof \Avenue\Request, 'Returned class instance is as expected via singleton.');
    }

    public function testSingletonThroughAppMagicCallMethod()
    {
        $this->app->container('request', function() {
            return new \Avenue\Request($this->app);
        });

        $instance = $this->app->request();
        $this->assertTrue($instance instanceof \Avenue\Request, 'Returned class instance is as expected via magic call method.');
    }

    /**
     * @expectedException LogicException
     */
    public function testCallAppNonExistMethodException()
    {
        $this->app->appNonExistMethod();
    }
}