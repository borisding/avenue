<?php
namespace Avenue\Tests;

use Avenue\App;

class PaginationTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $pagination;

    public function setUp()
    {
        $this->app = new App();
        $this->pagination = $this->app->pagination();
    }

    public function testGetPageLimitValue()
    {
        $this->pagination->set([
            'limit' => 10
        ]);
        $limit = $this->pagination->getPageLimit();
        $this->assertEquals(10, $limit);
    }

    public function testGetPageOffsetValue()
    {
        $_GET['page'] = 3;
        $this->pagination->set([
            'limit' => 20
        ]);
        $offset = $this->pagination->getPageOffset();
        $this->assertEquals(40, $offset);
    }

    public function testGetPageTotalValue()
    {
        $this->pagination->set([
            'total' => 100,
            'limit' => 10
        ]);
        $total = $this->pagination->getPageTotal();
        $this->assertEquals(10, $total);
    }

    public function testGetCurrentPageValue()
    {
        $_GET['page'] = 3;
        $this->pagination->set([
            'total' => 100,
            'limit' => 10
        ]);
        $total = $this->pagination->getCurrentPage();
        $this->assertEquals(3, $total);
    }

    public function testGetPreviousLabel()
    {
        $this->pagination->set([
            'previous' => 'Previous',
            'total' => 100,
            'limit' => 10
        ]);
        $label = $this->pagination->getPreviousLabel();
        $this->assertEquals('Previous', $label);
    }

    public function testGetNextLabel()
    {
        $this->pagination->set([
            'next' => 'Next',
            'total' => 100,
            'limit' => 10
        ]);
        $label = $this->pagination->getNextLabel();
        $this->assertEquals('Next', $label);
    }

    public function testGetPageLink()
    {
        $pageLink = 'http://localhost/product/list';
        $this->pagination->set([
            'link' => $pageLink,
            'total' => 100,
            'limit' => 10
        ]);
        $link = $this->pagination->getPageLink();
        $this->assertEquals($pageLink, $link);
    }

    public function testRenderPaginationHtml()
    {
        $this->pagination->set([
            'previous' => 'Previous',
            'next' => 'Next',
            'total' => 100,
            'limit' => 10,
            'link' => 'http://localhost/product/list'
        ]);
        $paginationHtml = $this->pagination->render();
        $this->assertEquals('string', gettype($paginationHtml));
    }
}