<?php
namespace Avenue\Tests;

use Avenue\App;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $v;

    public function setUp()
    {
        $this->app = new App();
        $this->v = $this->app->validation();
    }

    public function testInitWithParams()
    {
        $_POST['test'] = 'validation';
        $this->v->init($_POST);

        $value = $this->v->getFields('test');
        $this->assertEquals($_POST['test'], $value);
    }

    public function testInitWithoutParams()
    {
        $_POST['number'] = 123;
        $this->v->init();

        $value = $this->v->getFields('number');
        $this->assertEquals($_POST['number'], $value);
    }

    public function testIsRequiredFailed()
    {
        $_POST['param'] = '';
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsRequiredPassed()
    {
        $_POST['param'] = 'test';
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsRequiredWithNull()
    {
        $_POST['param'] = null;
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsRequiredWithSpaces()
    {
        $_POST['param'] = '     ';
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertFalse($this->v->hasPassed());
    }
}