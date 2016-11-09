<?php
namespace Avenue\Tests\State;

use Avenue\State\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    private $session;

    private $handler;

    public function setUp()
    {
        $this->handler = $this->getMockedHandler();
        $this->handler->expects($this->any())
        ->method('getAppSecret')
        ->will($this->returnCallback(function() {
            return 'testingsessionhandler';
        }));

        $this->session = new Session($this->handler);
    }

    private function getMockedHandler()
    {
        return $this->getMockBuilder('\Avenue\State\SessionDatabaseHandler')
        ->disableOriginalConstructor()
        ->getMock();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetSessionValue()
    {
        $this->session->set('foo', 'bar');
        $this->assertTrue(isset($_SESSION['foo']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetSessionValue()
    {
        $this->session->set('foo', 'bar');
        $this->assertEquals('bar', $this->session->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemoveSessionValue()
    {
        $this->session->set('foo', 'bar');
        $this->session->remove('foo');
        $this->assertEquals('', $this->session->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemoveAllSessionValues()
    {
        $this->session->set('foo', 'bar');
        $this->session->set('hello', 'world');
        $this->session->removeAll();
        $this->assertEquals(0, count($_SESSION));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetCsrfToken()
    {
        $this->session->setCsrfToken();
        $this->assertTrue(isset($_SESSION['csrfToken']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetCsrfToken()
    {
        $this->session->setCsrfToken();
        $this->assertEquals($this->session->getCsrfToken(), $_SESSION['csrfToken']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegenerateSessionId()
    {
        $this->assertTrue($this->session->regenerateId());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetSessionId()
    {
        $sessionId = $this->session->getId();
        $this->assertEquals($sessionId, session_id());
    }
}