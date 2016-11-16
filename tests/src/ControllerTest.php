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
        $this->app = new App(['timezone' => 'UTC'], uniqid(rand()));
        $this->app->route()->setParam('controller', 'foo');
        $this->app->route()->setParam('action', 'test');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testControllerActionLogicException()
    {
        $app = new App(['timezone' => 'UTC'], 'test-action');
        $app->route()->setParam('controller', 'foo');
        $app->route()->setParam('action', '');
        $stub = $this->getMock(Controller::class, ['indexAction', 'controllerAction'], [$app]);
        $stub->method('controllerAction')->willReturn(true);

        $foo = new FooController($app);
        $this->assertEquals($stub->controllerAction(), $foo->testAction());
    }

    public function testControllerActionInvoked()
    {
        $stub = $this->getMock(Controller::class, ['indexAction', 'controllerAction'], [$this->app]);
        $stub->method('controllerAction')->willReturn(true);

        $foo = new FooController($this->app);
        $this->assertEquals($stub->controllerAction(), $foo->testAction());
    }

    public function testControllerBeforeActionInvoked()
    {
        $stub = $this->getMock(Controller::class, ['indexAction', 'controllerAction'], [$this->app]);
        $stub->method('beforeAction')->willReturn(true);

        $foo = new FooController($this->app);
        $this->assertEquals($stub->beforeAction(), $foo->beforeAction());
    }

    public function testControllerAfterActionInvoked()
    {
        $stub = $this->getMock(Controller::class, ['indexAction', 'controllerAction'], [$this->app]);
        $stub->method('afterAction')->willReturn(true);

        $foo = new FooController($this->app);
        $this->assertEquals($stub->afterAction(), $foo->afterAction());
    }
}
