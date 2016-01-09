<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Log;
use Avenue\Exception;
use Avenue\Components\Encryption;
use Avenue\Components\Pagination;
use Avenue\Components\Validation;
use Avenue\Components\Cookie;
use Avenue\Components\Session;

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
        $request = $this->app->singleton('request');
        $this->assertTrue($request instanceof Request, 'Returned class instance is as expected via singleton.');
    }

    public function testSingletonThroughAppMagicCallMethod()
    {
        $request = $this->app->request();
        $this->assertTrue($request instanceof Request, 'Returned class instance is as expected via magic call method.');
    }

    public function testSingletonRequestClassInstance()
    {
        $request = $this->app->request();
        $this->assertTrue($request instanceof Request, '$request is instance of \Avenue\Request.');
    }

    public function testSingletonResponseClassInstance()
    {
        $response = $this->app->response();
        $this->assertTrue($response instanceof Response, '$response is instance of \Avenue\Response.');
    }

    public function testSingletonRouteClassInstance()
    {
        $route = $this->app->route();
        $this->assertTrue($route instanceof Route, '$route is instance of \Avenue\Route.');
    }

    public function testSingletonViewClassInstance()
    {
        $view = $this->app->view();
        $this->assertTrue($view instanceof View, '$view is instance of \Avenue\View.');
    }

    public function testSingletonLogClassInstance()
    {
        $log = $this->app->log();
        $this->assertTrue($log instanceof Log, '$log is instance of \Avenue\Log.');
    }

    public function testSingletonExceptionClassInstance()
    {
        $this->app->container('exception', function() {
            $exc = new \Exception();
            return new \Avenue\Exception(App::getInstance(), $exc);
        });

        $exception = $this->app->exception();
        $this->assertTrue($exception instanceof Exception, '$exception is instance of \Avenue\Exception.');
    }

    public function testSingletonEncryptionClassInstance()
    {
        $encryption = $this->app->encryption();
        $this->assertTrue($encryption instanceof Encryption, '$encryption is instance of \Avenue\Components\Encryption.');
    }

    public function testSingletonPaginationClassInstance()
    {
        $pagination = $this->app->pagination();
        $this->assertTrue($pagination instanceof Pagination, '$pagination is instance of \Avenue\Components\Pagination.');
    }

    public function testSingletonValidationClassInstance()
    {
        $validation = $this->app->validation();
        $this->assertTrue($validation instanceof Validation, '$validation is instance of \Avenue\Components\Validation.');
    }

    public function testSingletonCookieClassInstance()
    {
        $cookie = $this->app->cookie();
        $this->assertTrue($cookie instanceof Cookie, '$cookie is instance of \Avenue\Components\Cookie.');
    }

    public function testSingletonSessionClassInstance()
    {
        $session = $this->app->session();
        $this->assertTrue($session instanceof Session, '$session is instance of \Avenue\Components\Session.');
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
        // using reflection object class for retrieving static properties
        $ro = new \ReflectionObject($this->app);
        $sp = $ro->getStaticProperties();

        // using 'version' key for testing
        $this->assertEquals($this->app->getConfig('version'), $sp['config']['version'], 'Config value should be same.');
    }

    public function testGetVersion()
    {
        $this->assertEquals($this->app->getVersion(), App::AVENUE_VERSION, 'Version should be equal.');
    }
}