<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Exception;
use Avenue\Mcrypt;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        @session_start();
        $this->app = new App([
            'appVersion' => '1.0',
            'httpVersion' => '1.1',
            'timezone' => 'UTC',
            'environment' => 'development',
            'defaultController' => 'default',
            'foo' => 'bar'
        ]);
    }

    public function testGetInstance()
    {
        $this->assertEquals(App::getInstance(), $this->app);
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
        $this->assertEquals(false, $fulfilled);
    }

    /**
     * @expectedException LogicException
     */
    public function testAddRouteNotReturningArrayException()
    {
        $this->app->addRoute('param1', function() {
            return '';
        });
    }

    /**
     * @expectedException Exception
     */
    public function testRenderPageNotFoundException()
    {
        $this->app->addRoute('/test', function() {
            return [];
        });

        $this->app->run();
    }

    public function testContainer()
    {
        $this->app->container('calculation', function() {
            $result = 1 + 1;
            return $result;
        });

        $this->assertEquals(2, $this->app->resolve('calculation'));
    }

    public function testContainerWithAppInstancePassedToCallback()
    {
        $this->app->container('getAppInstance', function($app) {
            return $app;
        });

        $app = $this->app->resolve('getAppInstance');
        $this->assertTrue($app instanceof App);
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSingletonThrowsExceptionForNonObject()
    {
        $this->app->container('classObjectNotExist', function() {
            return 'I am just a string.';
        });

        $this->app->singleton('classObjectNotExist');
    }

    public function testSingletonReturnsClassInstance()
    {
        $request = $this->app->singleton('request');
        $this->assertTrue($request instanceof Request);
    }

    public function testSingletonThroughAppMagicCallMethod()
    {
        $request = $this->app->request();
        $this->assertTrue($request instanceof Request);
    }

    public function testSingletonRequestClassInstance()
    {
        $request = $this->app->request();
        $this->assertTrue($request instanceof Request);
    }

    public function testSingletonRequestClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->request(), App::request());
    }

    public function testSingletonResponseClassInstance()
    {
        $response = $this->app->response();
        $this->assertTrue($response instanceof Response);
    }

    public function testSingletonResponseClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->response(), App::response());
    }

    public function testSingletonRouteClassInstance()
    {
        $route = $this->app->route();
        $this->assertTrue($route instanceof Route);
    }

    public function testSingletonRouteClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->route(), App::route());
    }

    public function testSingletonViewClassInstance()
    {
        $view = $this->app->view();
        $this->assertTrue($view instanceof View);
    }

    public function testSingletonViewClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->view(), App::view());
    }

    public function testSingletonExceptionClassInstance()
    {
        $this->app->container('exception', function() {
            $exc = new \Exception();
            return new \Avenue\Exception(App::getInstance(), $exc);
        });

        $exception = $this->app->exception();
        $this->assertTrue($exception instanceof Exception);
    }

    public function testSingletonExceptionClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->exception(), App::exception());
    }

    public function testSingletonMcrytClassInstance()
    {
        $mcrypt = $this->app->mcrypt();
        $this->assertTrue($mcrypt instanceof Mcrypt);
    }

    public function testSingletonMcryptClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->mcrypt(), App::mcrypt());
    }

    /**
     * @expectedException LogicException
     */
    public function testCallAppNonExistMethodException()
    {
        $this->app->appNonExistMethod();
    }

    /**
     * @expectedException LogicException
     */
    public function testCallAppNonExistStaticMethodException()
    {
        App::appNonExistMethod();
    }

    public function testGetConfig()
    {
        $this->assertEquals('bar', $this->app->getConfig('foo'));
    }

    public function testGetAllConfig()
    {
        $this->assertTrue(count($this->app->getConfig()) > 0);
    }

    public function testGetAppVersion()
    {
        $this->assertEquals('1.0', $this->app->getAppVersion());
    }

    public function testGetHttpVersion()
    {
        $this->assertEquals('1.1', $this->app->getHttpVersion());
    }

    public function testGetTimezone()
    {
        $this->assertEquals('UTC', $this->app->getTimezone());
    }

    public function testGetEnvironment()
    {
        $this->assertEquals('development', $this->app->getEnvironment());
    }

    public function testGetDefaultController()
    {
        $this->assertEquals('default', $this->app->getDefaultController());
    }
}