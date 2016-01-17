<?php
namespace Avenue\Tests;

use Avenue\App;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $cookie;

    public function setUp()
    {
        $this->app = new App();
        $this->cookie = $this->app->cookie();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyCookieKeyException()
    {
        $this->cookie->set('', 'test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyCookieSecretException()
    {
        Reflection::setPropertyValue(new App(), 'config', ['timezone' => 'UTC', 'cookie' => []], true);
        $this->getMock('\Avenue\Components\Cookie', [], [$this->app]);
    }

    public function testCookieValue()
    {
        $_COOKIE['foo'] = 'bar';
        Reflection::setPropertyValue(new App(), 'config', ['timezone' => 'UTC', 'cookie' => ['secret' => 'thisisdummysecretkey']], true);
        $cookie = $this->getMock('\Avenue\Components\Cookie', ['verify', 'decrypt'], [$this->app]);
        $cookie->method('verify')->willReturn('bar');
        $cookie->method('decrypt')->willReturn('bar');
        $this->assertEquals($_COOKIE['foo'], $cookie->get('foo'));
    }

    public function testRemoveCookieValue()
    {
        $_COOKIE['foo'] = '';
        Reflection::setPropertyValue(new App(), 'config', ['timezone' => 'UTC', 'cookie' => ['secret' => 'thisisdummysecretkey']], true);
        $cookie = $this->getMock('\Avenue\Components\Cookie', ['remove'], [$this->app]);
        $cookie->method('remove')->willReturn('');
        $this->assertEquals($_COOKIE['foo'], $cookie->remove('foo'));
    }
}