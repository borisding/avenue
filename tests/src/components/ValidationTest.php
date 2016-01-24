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

    public function testEqualLengthPassed()
    {
        $_POST['author'] = 'Boris Ding Poh Hing';
        $this->v->init();
        $this->v->equalLength('author', 19);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testEqualLengthFailed()
    {
        $_POST['author'] = 'Boris Ding Poh Hing';
        $this->v->init();
        $this->v->equalLength('author', 20);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testMinLengthPassed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->minLength('input', 2);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testMinLengthFailed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->minLength('input', 4);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testMaxLengthPassed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->maxLength('input', 4);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testMaxLengthFailed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->maxLength('input', 2);
        $this->assertFalse($this->v->hasPassed());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInRangeLengthInvalidArgumentException()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->inRangeLength('input', [2, 3, 4]);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testInRangeLengthPassed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->inRangeLength('input', [2, 3]);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testInRangeLengthFailed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->inRangeLength('input', [1, 2]);
        $this->assertFalse($this->v->hasPassed());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInRangeInvalidArgumentException()
    {
        $_POST['dummy'] = 2;
        $this->v->init();
        $this->v->inRange('dummy', [2, 3, 4]);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testInRangePassed()
    {
        $_POST['dummy'] = 2;
        $this->v->init();
        $this->v->inRange('dummy', [2, 4]);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testInRangeFailed()
    {
        $_POST['dummy'] = 2;
        $this->v->init();
        $this->v->inRange('dummy', [2, 4]);
        $this->assertTrue($this->v->hasPassed());
    }
}