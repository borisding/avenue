<?php
namespace Avenue\Tests;

use Avenue\App;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $view;

    public function setUp()
    {
        $this->app = new App(['timezone' => 'UTC'], uniqid(rand()));
        $this->view = $this->app->view();
    }

    public function testFetch()
    {
        $view = $this->getMock('\Avenue\View', ['getViewFile'], [$this->app]);
        $view->method('getViewFile')->willReturn(AVENUE_TESTS_DIR . '/src/views/user.php');
        $output = $view->fetch('user', ['name' => 'Boris']);
        $this->assertEquals('hey! Boris', $output);
    }

    public function testLayout()
    {
        $view = $this->getMock('\Avenue\View', ['getViewFile'], [$this->app]);
        $view->method('getViewFile')->willReturn(AVENUE_TESTS_DIR . '/src/views/layouts/admin.php');
        $output = $view->layout('admin');
        $this->assertEquals('admin page', $output);
    }

    public function testPartial()
    {
        $view = $this->getMock('\Avenue\View', ['getViewFile'], [$this->app]);
        $view->method('getViewFile')->willReturn(AVENUE_TESTS_DIR . '/src/views/partials/content.php');
        $output = $view->partial('content');
        $this->assertEquals('content only', $output);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisteredHelperNameAlreadyExistedException()
    {
        $this->view->register('test', function() {});
        $this->view->register('test', function() {});
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisteredHelperInvalidNameException()
    {
        $this->view->register('~!@#test', function() {});
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvokeInvalidHelper()
    {
        $this->view->registeredHelper();
    }

    public function testRegisteredHelperReturnsUpperCase()
    {
        $this->view->register('upper', function() {
            return strtoupper('boris');
        });

        $output = $this->view->upper();
        $this->assertEquals('BORIS', $output);
    }
}
