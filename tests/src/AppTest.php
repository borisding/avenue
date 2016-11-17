<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Exception;
use Avenue\Crypt;
use Avenue\State\Cookie;
use Avenue\State\Session;
use Avenue\Tests\Reflection;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $config = [
        'secret' => 'secretfortestonly',
        'appVersion' => '1.0',
        'httpVersion' => '1.1',
        'timezone' => 'UTC',
        'environment' => 'development',
        'defaultController' => 'default',
        'foo' => 'bar',
        'state' => [
            'cookie' => [],
            'session' => []
        ]
    ];

    public function setUp()
    {
        $this->app = new App($this->config, uniqid(rand()));
    }

    public function testGetInstance()
    {
        $this->assertEquals(App::getInstance(), $this->app);
    }

    public function testGetAppId()
    {
        $id = 'test-id';
        $app = new App($this->config, $id);
        $this->assertEquals($id, $app->getId());
    }

    /**
     * @expectedException LogicException
     */
    public function testDuplicateServiceName()
    {
        $app = new App($this->config, 'test-duplicate');
        $app->container('test', function() {});
        $app->container('test', function() {});
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRouteInvalidNumberOfParameters()
    {
        $this->app->addRoute('param1', 'param2', []);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRouteSecondParamIsNotAssociativeArray()
    {
        $this->app->addRoute('param1', 'param2');
    }

    public function testAddRouteSecondParamIsAssociativeArray()
    {
        $this->app->addRoute('param1', [
            'foo' => 'bar'
        ]);

        // if route has passed, expects fullfiled is boolean
        $fulfilled = $this->app->route()->isFulfilled();
        $this->assertEquals(false, $fulfilled);
    }

    /**
     * @expectedException Exception
     */
    public function testRenderPageNotFoundException()
    {
        $this->app->addRoute('/test', []);
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

    public function testContainerReceivedAppInCallback()
    {
        $this->app->container('testapp', function($app) {
            $this->assertTrue($app instanceof App);
        });
    }

    public function testSingletonContainer()
    {
        $this->app->singleton('object', function() {
            return new \stdClass;
        });

        $this->assertTrue(is_object($this->app->resolveSingleton('object')));
    }

    public function testSingletonContainerReceivedAppInCallback()
    {
        $this->app->singleton('testapp', function($app) {
            $this->assertTrue($app instanceof App);
            return new \stdClass;
        });
    }

    public function testContainerWithAppInstancePassedToCallback()
    {
        $this->app->container('getAppInstance', function() {
            return $this->app;
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
     * @expectedException LogicException
     */
    public function testResolveLogicException()
    {
        $this->app->resolve('serviceDoesNotExist');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSingletonThrowsExceptionForNonObject()
    {
        $this->app->singleton('classObjectNotExist', function($app) {
            return 'I am just a string.';
        });

        $this->app->resolveSingleton('classObjectNotExist');
    }

    public function testSingletonReturnsClassInstance()
    {
        $request = $this->app->resolveSingleton('request');
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
        $app = new App($this->config, uniqid(rand()));
        $app->singleton('fakeException', function() use ($app) {
            return new Exception($app, new \Exception('test exception'));
        });

        $exception = $app->fakeException();
        $this->assertTrue($exception instanceof Exception);
        $this->assertTrue($exception->getBaseInstance() instanceof \Exception);
    }

    public function testSingletonExceptionClassInstanceViaStaticMethod()
    {
        $app = new App($this->config, uniqid(rand()));
        $app->singleton('fakeException', function() use ($app) {
            return new Exception($app, new \Exception('test exception'));
        });

        $this->assertEquals($app->fakeException(), App::fakeException());
    }

    public function testSingletonCryptClassInstance()
    {
        $crypt = $this->app->crypt();
        $this->assertTrue($crypt instanceof Crypt);
    }

    public function testSingletonCryptClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->crypt(), App::crypt());
    }

    public function testSingletonCookieClassInstance()
    {
        $cookie = $this->app->cookie();
        $this->assertTrue($cookie instanceof Cookie);
    }

    public function testSingletonCookieClassInstanceViaStaticMethod()
    {
        $this->assertEquals($this->app->cookie(), App::cookie());
    }

    private function getMockedSessionContainer($static = false)
    {
        @session_start();

        $mockedHandler = $this
        ->getMockBuilder('\Avenue\State\SessionDatabaseHandler')
        ->disableOriginalConstructor()
        ->getMock();

        $app = new App($this->config, uniqid(rand()));
        $app->singleton('fakeSession', function() use ($mockedHandler) {
            return new Session($mockedHandler);
        });

        if ($static) {
            return App::fakeSession();
        }

        return $app->fakeSession();
    }

    public function testSingletonSessionClassInstance()
    {
        $session = $this->getMockedSessionContainer();
        $this->assertTrue($session instanceof Session);
    }

    public function testSingletonSessionClassInstanceViaStaticMethod()
    {
        $session = $this->getMockedSessionContainer(true);
        $this->assertTrue($session instanceof Session);
    }

    public function testResolveServiceWithParametersInCallback()
    {
        $this->app->container('greeting', function($app, $param1, $param2) {
            return sprintf('%s %s', $param1, $param2);
        });

        $this->assertEquals('hello world', $this->app->resolve('greeting', ['hello', 'world']));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCallAppNonExistMethodException()
    {
        $this->app->appNonExistMethod();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCallAppNonExistStaticMethodException()
    {
        App::appNonExistMethod();
    }

    /**
     * @expectedException LogicException
     */
    public function testGetConfigInvalidArgumentException()
    {
        $app = new App();
        $app->getConfig();
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

    public function testGetSecret()
    {
        $this->assertEquals('secretfortestonly', $this->app->getSecret());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetLocaleInvalidArgumentExceptionForEmptyValue()
    {
        $this->app->setLocale(null);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSetLocaleRuntimeExceptionFileNotFound()
    {
        $this->app->setLocale('en-US', 'path/to/en-US.php');
    }

    public function testSetGetLocale()
    {
        $locale = 'en-US';
        $this->app->setLocale($locale);
        $this->assertEquals($locale, $this->app->getLocale());
    }

    private function loadTranslationFile()
    {
        return $this->app->setLocale('zh-CN', __DIR__ . '/Mocks/zh-CN.php');
    }

    public function testValidTranslation()
    {
        $this->loadTranslationFile();
        $this->assertEquals('您好', $this->app->t('text.name'));
        $this->assertEquals('符号 ¥', $this->app->t('测试.符号', ['¥']));
    }

    public function testValidTranslationWithPlaceholders()
    {
        $this->loadTranslationFile();
        $this->assertEquals('嗨！小明. 你见到小强了吗？', $this->app->t('text.hasPlaceholders', ['小明', '小强']));
    }

    public function testValidTranslationWithInvalidPlaceholers()
    {
        $this->loadTranslationFile();
        $this->assertEquals('嗨！{ 0 }. 你见到{ 1}了吗？', $this->app->t('text.invalidPlaceholders', ['小明', '小强']));
    }

    public function testInvalidTranslations()
    {
        $this->loadTranslationFile();

        $this->assertEquals('text.name.more', $this->app->t('text.name.more'));
        $this->assertEquals('text..name', $this->app->t('text..name'));
        $this->assertEquals('textname', $this->app->t('textname'));
        $this->assertEquals('', $this->app->t(''));
    }
}
