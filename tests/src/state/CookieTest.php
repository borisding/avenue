<?php
namespace Avenue\Tests\State;

use Avenue\App;
use Avenue\State\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyCookieSecretException()
    {
        $cookie = new Cookie($this->app, []);
        $cookie->set('test', 'dummp');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyCookieKeyException()
    {
        $cookie = new Cookie($this->app, ['secret' => 'thisissecretkey']);
        $cookie->set('', 'test');
    }

    /**
     * @runInSeparateProcess
     */
    public function testCookieValue()
    {
        $config = ['secret' => 'mysecret', 'encrypt' => false];
        $cookie = new Cookie($this->app, $config);
        $cookie->set('test', 123);
        $this->assertEquals(123, $cookie->get('test'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemoveCookieValue()
    {
        $config = ['secret' => 'mysecret', 'encrypt' => false];
        $cookie = new Cookie($this->app, $config);
        $cookie->set('foo', 'bar');
        $cookie->remove('foo');
        $this->assertEmpty($cookie->get('foo'));
    }
}