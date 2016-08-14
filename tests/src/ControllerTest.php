<?php
namespace Avenue\Tests;

use Avenue\App;
use App\Controllers\FooController;
use Avenue\Tests\Reflection;

require_once 'mocks/FooController.php';

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App();
        $this->app->route->setParam('controller', 'foo');
        $this->app->route->setParam('action', 'test');
        $this->app->route->setParam('id', '123');
    }

    public function testControllerIndexActionInvoked()
    {
        $this->app->route->setParam('action', 'index');
        $indexAction = Reflection::callClassMethod(new FooController($this->app), 'indexAction');
        $this->assertTrue($indexAction);
    }

    public function testControllerBeforeActionInvoked()
    {
        $beforeAction = Reflection::callClassMethod(new FooController($this->app), 'beforeAction');
        $this->assertTrue($beforeAction);
    }

    public function testControllerActionInvoked()
    {
        $controllerAction = Reflection::callClassMethod(new FooController($this->app), 'controllerAction');
        $this->assertTrue($controllerAction);
    }

    public function testControllerAfterActionInvoked()
    {
        $afterAction = Reflection::callClassMethod(new FooController($this->app), 'afterAction');
        $this->assertTrue($afterAction);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testControllerNonExistActionException()
    {
        $this->app->route->setParam('action', 'dummy');
        $controllerAction = Reflection::callClassMethod(new FooController($this->app), 'controllerAction');
    }

    public function testControllerRequestEqualToAppRequest()
    {
        $this->assertAttributeEquals($this->app->request, 'request', new FooController($this->app));
    }

    public function testControllerResponseEqualToAppResponse()
    {
        $this->assertAttributeEquals($this->app->response, 'response', new FooController($this->app));
    }

    public function testControllerRouteEqualToAppRoute()
    {
        $this->assertAttributeEquals($this->app->route, 'route', new FooController($this->app));
    }

    public function testControllerViewEqualToAppView()
    {
        $this->assertAttributeEquals($this->app->view, 'view', new FooController($this->app));
    }
}
