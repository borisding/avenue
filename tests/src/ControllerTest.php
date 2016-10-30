<?php
namespace Avenue\Tests;

require_once 'mocks/FooController.php';

use Avenue\App;
use Avenue\Controller;
use App\Controllers\FooController;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App(['timezone' => 'UTC']);
        $this->app->route->setParam('controller', 'foo');
        $this->app->route->setParam('action', 'test');
    }

    public function testControllerActionLogicException()
    {
        $this->app->route->setParam('controller', null);
        $stub = $this->getMock(Controller::class, ['indexAction', 'controllerAction'], [$this->app]);
        $stub->method('controllerAction')->willReturn(true);

        $foo = new FooController($this->app);
        $this->assertEquals($stub->controllerAction(), $foo->testAction());
    }

    public function testControllerActionInvoked()
    {
        $stub = $this->getMock(Controller::class, ['indexAction', 'controllerAction'], [$this->app]);
        $stub->method('controllerAction')->willReturn(true);

        $foo = new FooController($this->app);
        $this->assertEquals($stub->controllerAction(), $foo->testAction());
    }
}