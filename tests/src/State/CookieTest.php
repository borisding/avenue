<?php
namespace Avenue\Tests\State;

use Avenue\App;
use Avenue\State\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $cookie;

    public function setUp()
    {
        $app = new App([
            'secret' => 'secretfortestingcookie',
            'timezone' => 'UTC',
            'state' => ['cookie' => []]
        ], uniqid(rand()));

        $this->cookie = new Cookie($app);
    }

    /**
     * @runInSeparateProcess
     * @expectedException InvalidArgumentException
     */
    public function testEmptyCookieSecretException()
    {
        $app = new App([
            'timezone' => 'UTC',
            'state' => ['cookie' => []]
        ], uniqid(rand()));
        $cookie = new Cookie($app);
        $cookie->set('test', 'dummp');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyCookieKeyException()
    {
        $this->cookie->set('', 'test');
    }

    /**
     * @runInSeparateProcess
     */
    public function testCookieValue()
    {
        $this->cookie->set('test', 123);
        $this->assertEquals(123, $this->cookie->get('test'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemoveCookieValue()
    {
        $this->cookie->set('foo', 'bar');
        $this->cookie->remove('foo');
        $this->assertEmpty($this->cookie->get('foo'));
    }
}
