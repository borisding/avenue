<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Exception;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new App(['timezone' => 'UTC'], uniqid(rand()));
    }

    public function testGetBaseExceptionInstance()
    {
        $message = 'yo! I am exception.';
        $exception = new Exception($this->app, new \Exception($message));
        $this->assertTrue($exception->getBaseInstance() instanceof \Exception);
    }

    public function testRenderExceptionReturnStringOutput()
    {
        $message = 'yo! I am exception.';
        $exception = $this->getMock('\Avenue\Exception', ['__toString'], [$this->app, new \Exception($message)]);
        $exception->method('__toString')->willReturn($message);

        $output = (string)$exception;
        $this->assertEquals($message, $output);
    }

    public function testRuntimeExceptionMessage()
    {
        $message = 'yo! I am runtime exception.';
        $exception = new Exception($this->app, new \RuntimeException($message));
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testLogicExceptionMessage()
    {
        $message = 'yo! I am logic exception.';
        $exception = new Exception($this->app, new \LogicException($message));
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testInvalidArgumentExceptionMessage()
    {
        $message = 'yo! I am invalid argument exception.';
        $exception = new Exception($this->app, new \LogicException($message));
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testGetExceptionCode()
    {
        $code = 100;
        $exception = new Exception($this->app, new \Exception('yo! I am exception.', $code));
        $this->assertEquals($code, $exception->getCode());
    }

    public function testBothCustomExceptionAndCoreExceptionMessageAreTheSame()
    {
        $base = new \Exception('yo! I am exception.');
        $exception = new Exception($this->app, $base);
        $this->assertEquals($exception->getMessage(), $base->getMessage());
    }

    public function testBothCustomExceptionAndCoreExceptionCodeAreTheSame()
    {
        $base = new \Exception('yo! I am exception.', 100);
        $exception = new Exception($this->app, $base);
        $this->assertEquals($exception->getCode(), $base->getCode());
    }

    public function testBothCustomExceptionAndCoreExceptionLineAreTheSame()
    {
        $base = new \Exception('yo! I am exception.');
        $exception = new Exception($this->app, $base);
        $this->assertEquals($exception->getLine(), $base->getLine());
    }

    public function testBothCustomExceptionAndCoreExceptionTraceAreTheSame()
    {
        $base = new \Exception('yo! I am exception.');
        $exception = new Exception($this->app, $base);
        $this->assertEquals($exception->getTrace(), $base->getTrace());
    }

    public function testBothCustomExceptionAndCoreExceptionTraceStringAreTheSame()
    {
        $base = new \Exception('yo! I am exception.');
        $exception = new Exception($this->app, $base);
        $this->assertEquals($exception->getTraceAsString(), $base->getTraceAsString());
    }

    public function testGetThrownExceptionClassName()
    {
        $exception = new Exception($this->app, new \RuntimeException('yo! I am runtime exception.'));
        $this->assertEquals('RuntimeException', $exception->getExceptionClass());
    }

    public function testGetAppIDInException()
    {
        $appId = 'testexceptionappid';
        $app = new App(['timezone' => 'UTC'], $appId);

        $exception = new Exception($this->app, new \RuntimeException('yo! I am runtime exception.'));
        $this->assertEquals($appId, $exception->getAppId());
    }
}
