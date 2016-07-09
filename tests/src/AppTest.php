<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Log;
use Avenue\Exception;
use Avenue\Tests\Reflection;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        @session_start();
        $this->app = new App();
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

        $this->app->render();
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

    public function testSingletonResponseClassInstance()
    {
        $response = $this->app->response();
        $this->assertTrue($response instanceof Response);
    }

    public function testSingletonRouteClassInstance()
    {
        $route = $this->app->route();
        $this->assertTrue($route instanceof Route);
    }

    public function testSingletonViewClassInstance()
    {
        $view = $this->app->view();
        $this->assertTrue($view instanceof View);
    }

    public function testSingletonLogClassInstance()
    {
        $log = $this->app->log();
        $this->assertTrue($log instanceof Log);
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

    /**
     * @expectedException LogicException
     */
    public function testCallAppNonExistMethodException()
    {
        $this->app->appNonExistMethod();
    }

    public function testGetConfig()
    {
        Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'foo' => 'bar'], true);
        $this->assertEquals('bar', $this->app->getConfig('foo'));
    }

    public function testGetVersion()
    {
        $this->assertEquals(App::AVENUE_VERSION, $this->app->getVersion(), 'Version should be equal.');
    }

    public function testGetAppVersion()
    {
        Reflection::setPropertyValue($this->app, 'config', ['version' => '1.0', 'timezone' => 'UTC'], true);
        $this->assertEquals('1.0', $this->app->getAppVersion());
    }

    public function testGetHttpVersion()
    {
        Reflection::setPropertyValue($this->app, 'config', ['http' => '1.1', 'timezone' => 'UTC'], true);
        $this->assertEquals('1.1', $this->app->getHttpVersion());
    }

    public function testGetTimezone()
    {
        Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC'], true);
        $this->assertEquals('UTC', $this->app->getTimezone());
    }

    public function testGetEnvironment()
    {
        Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'environment' => 'development'], true);
        $this->assertEquals('development', $this->app->getEnvironment());
    }

    public function testGetDefaultController()
    {
        Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'defaultController' => 'default'], true);
        $this->assertEquals('default', $this->app->getDefaultController());
    }
}