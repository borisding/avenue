<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Tests\Reflection;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $session;

    private $config = [
        // type of storage (file, cookie or database)
        'storage' => 'file',

        // table name for database storage
        'table' => 'session',

        // the save path for file storage
        'path' => '',

        // session lifetime in seconds for garbage collector
        'lifetime' => 3600,

        // whether to encrypt session's value
        // if storage is cookie, should refer to cookie's encrypt setting instead
        'encrypt' => true
    ];

    public function setUp()
    {
        $this->app = new App();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptySessionKeyException()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('', 'test');
    }

    public function testSetSessionValueWithFileStorage()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $this->assertEquals($_SESSION['foo'], 'bar');
    }

    public function testGetSessionValueWithFileStorage()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $this->assertEquals($this->session->get('foo'), 'bar');
    }

    public function testSetSessionValueWithCookieStorage()
    {
        $this->setCookieStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $this->assertEquals($_SESSION['foo'], 'bar');
    }

    public function testGetSessionValueWithCookieStorage()
    {
        $this->setCookieStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $this->assertEquals($this->session->get('foo'), 'bar');
    }

    public function testSetSessionValueWithDatabaseStorage()
    {
        $this->setDatabaseStorage();
        $this->session = $this->app->session();

        $config = $this->app->getConfig('session');
        $sessionDb = $this->getMock('\Avenue\Components\SessionDatabase');
        $this->app->container('session', function() use ($sessionDb, $config) {
            return new Session($sessionDb, $config);
        });

        $this->session->set('foo', 'bar');
        $this->assertEquals($_SESSION['foo'], 'bar');
    }

    public function testGetSessionValueWithDatabaseStorage()
    {
        $this->setDatabaseStorage();
        $this->session = $this->app->session();

        $config = $this->app->getConfig('session');
        $sessionDb = $this->getMock('\Avenue\Components\SessionDatabase');
        $this->app->container('session', function() use ($sessionDb, $config) {
            return new Session($sessionDb, $config);
        });

        $this->session->set('foo', 'bar');
        $this->assertEquals($this->session->get('foo'), 'bar');
    }

    public function testRemoveSessionValue()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $this->session->remove('foo');
        $this->assertEquals($this->session->get('foo'), '');
    }

    public function testRemoveAllSessionValues()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $this->session->set('test', 'dummy');
        $this->session->removeAll();
        $this->assertEquals(count($_SESSION), 0);
    }

    public function testRegenerateSessionId()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $newSessionId = $this->session->regenerateId();
        $this->assertEquals(session_id(), $newSessionId);
    }

    public function testGetSessionId()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $this->session->set('foo', 'bar');
        $sessionId = $this->session->getId();
        $this->assertEquals(session_id(), $sessionId);
    }

    public function testGetCsrfToken()
    {
        $this->setFileStorage();
        $this->session = $this->app->session();
        $csrfToken = $this->session->getCsrfToken();
        $this->assertEquals($this->session->get('csrfToken'), $csrfToken);
    }

    public function setFileStorage()
    {
        $this->config['storage'] = 'file';
        return Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'session' => $this->config], true);
    }

    public function setCookieStorage()
    {
        $this->config['storage'] = 'cookie';
        return Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'session' => $this->config], true);
    }

    public function setDatabaseStorage()
    {
        $this->config['storage'] = 'database';
        return Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'session' => $this->config], true);
    }
}