<?php
namespace Avenue\Tests;

use Avenue\App;
use App\Controllers\FooController;

// include vendor autoloader
$autoloader = require AVENUE_VENDOR_DIR  . '/autoload.php';

// set tests namespace at runtime
$autoloader->setPsr4('App\\Controllers\\', __DIR__ . '/fixtures/');

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App();
        $this->app->route->setParam('@controller', 'foo');
        $this->app->route->setParam('@action', 'test');
        $this->app->route->setParam('@id', '123');
    }

    public function testControllerIndexActionInvoked()
    {
        $this->app->route->setParam('@action', 'index');
        $indexAction = $this->getClassMethod(new FooController($this->app), 'indexAction');
        $this->assertTrue($indexAction);
    }

    public function testControllerBeforeActionInvoked()
    {
        $beforeAction = $this->getClassMethod(new FooController($this->app), 'beforeAction');
        $this->assertTrue($beforeAction);
    }

    public function testControllerActionInvoked()
    {
        $controllerAction = $this->getClassMethod(new FooController($this->app), 'controllerAction');
        $this->assertTrue($controllerAction);
    }

    public function testControllerAfterActionInvoked()
    {
        $afterAction = $this->getClassMethod(new FooController($this->app), 'afterAction');
        $this->assertTrue($afterAction);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testControllerNonExistActionException()
    {
        $this->app->route->setParam('@action', 'dummy');
        $controllerAction = $this->getClassMethod(new FooController($this->app), 'controllerAction');
    }

    // This is reusable code to get controller class method
    public function getClassMethod($obj, $method, array $params = [])
    {
        $rc = new \ReflectionClass(get_class($obj));
        $cm = $rc->getMethod($method);
        $cm->setAccessible(true);

        return $cm->invokeArgs($obj, $params);
    }
}