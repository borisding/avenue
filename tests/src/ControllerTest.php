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
        $request = Reflection::getPropertyValue(new FooController($this->app), 'request');
        $this->assertEquals($request, $this->app->request);
    }

    public function testControllerResponseEqualToAppResponse()
    {
        $response = Reflection::getPropertyValue(new FooController($this->app), 'response');
        $this->assertEquals($response, $this->app->response);
    }

    public function testControllerRouteEqualToAppRoute()
    {
        $route = Reflection::getPropertyValue(new FooController($this->app), 'route');
        $this->assertEquals($route, $this->app->route);
    }

    public function testControllerViewEqualToAppView()
    {
        $view = Reflection::getPropertyValue(new FooController($this->app), 'view');
        $this->assertEquals($view, $this->app->view);
    }
}
