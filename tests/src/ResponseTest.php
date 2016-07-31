<?php
namespace Avenue\Tests;

use Avenue\App;
use Avenue\Response;
use Avenue\Tests\Reflection;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $response;

    public function setUp()
    {
        $this->app = new App();
        $this->response = $this->app->response();
    }

    public function testStatusDescriptionsNotEmpty()
    {
        $statusDescriptions = Reflection::getPropertyValue($this->response, 'statusDescriptions');
        $this->assertNotEmpty($statusDescriptions);
    }

    public function testWriteBody()
    {
        $this->response->write('hello world!');
        $this->assertAttributeEquals('hello world!', 'body', $this->response);
    }

    public function testGetStatusCode()
    {
        $this->response->withStatus(200);
        $statusCode = $this->response->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }

    public function testGetStatusDescription()
    {
        $this->response->withStatus(200);
        $statusDescription = $this->response->getStatusDescription(200);
        $this->assertEquals('OK', $statusDescription);
    }

    public function testWithStatusCode()
    {
        $this->response->withStatus(404);
        $this->assertAttributeEquals(404, 'statusCode', $this->response);
    }

    public function testWithStatusDescription()
    {
        $this->response->withStatus(404);
        $statusDescriptions = Reflection::getPropertyValue($this->response, 'statusDescriptions');
        $expected = $statusDescriptions[404];
        $actual = $this->response->getStatusDescription(404);
        $this->assertEquals($expected, $actual);
    }

    public function testOK()
    {
        $this->response->withStatus(200);
        $this->assertEquals('OK', $this->response->getStatusDescription(200));
    }

    public function testNotFound()
    {
        $this->response->withStatus(404);
        $this->assertEquals('Not Found', $this->response->getStatusDescription(404));
    }

    public function testInternalServerError()
    {
        $this->response->withStatus(500);
        $this->assertEquals('Internal Server Error', $this->response->getStatusDescription(500));
    }

    public function testNotModified()
    {
        $this->response->withStatus(304);
        $this->assertEquals('Not Modified', $this->response->getStatusDescription(304));
    }

    public function testCleanup()
    {
        $this->response->write('this is just a string!');
        $this->response->cleanup();
        $this->assertEmpty($this->response->getBody());
    }

    public function testGetBody()
    {
        $this->response->cleanup();
        $this->response->write('expect output!');
        $this->assertEquals('expect output!', $this->response->getBody());
    }

    public function testGetHeader()
    {
        $this->response->withHeader(['hello' => 'world']);
        $this->assertEquals('world', $this->response->getHeader('hello'));
    }

    public function testWithHeader()
    {
        $this->response->withHeader(['test' => '123']);
        $headers = Reflection::getPropertyValue($this->response, 'headers');
        $this->assertArrayHasKey('test', $headers);
    }

    public function testWithJsonHeader()
    {
        $this->response->withJsonHeader();
        $this->assertEquals('application/json;charset=utf-8', $this->response->getHeader('Content-Type'));
    }

    public function testWithTextHeader()
    {
        $this->response->withTextHeader();
        $this->assertEquals('text/plain', $this->response->getHeader('Content-Type'));
    }

    public function testWithCsvHeader()
    {
        $this->response->withCsvHeader();
        $this->assertEquals('text/csv', $this->response->getHeader('Content-Type'));
    }

    public function testWithXmlHeader()
    {
        $this->response->withXmlHeader();
        $this->assertEquals('text/xml', $this->response->getHeader('Content-Type'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowInvalidArgumentExceptionOfETag()
    {
        $this->response->withEtag('testetagdummpy', 'test');
    }

    public function testWithEtagWithStrongHeader()
    {
        $this->response->withEtag('testetagstrong');
        $etagHeader = $this->response->getHeader('ETag');
        $this->assertTrue(substr($etagHeader, 0, 2) !== 'W/');
    }

    public function testWithEtagWithWeakHeader()
    {
        $this->response->withEtag('testetagweak', 'weak');
        $etagHeader = $this->response->getHeader('ETag');
        $this->assertTrue(substr($etagHeader, 0, 2) === 'W/');
    }

    public function testWithLastModifiedHeader()
    {
        $timestamp = 1469971458;
        $this->response->withLastModified($timestamp);
        $lastModified = $this->response->getHeader('Last-Modified');
        $this->assertEquals(gmdate('D, d M Y H:i:s', $timestamp) . ' GMT', $lastModified);
    }

    public function testWithCacheHeader()
    {
        $expireTime = strtotime('+1 week');
        $this->response->cache($expireTime);
        $expires = $this->response->getHeader('Expires');
        $this->assertEquals(gmdate('D, d M Y H:i:s', $expireTime) . ' GMT', $expires);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowInvalidArgumentExceptionOfGetGmtDateTime()
    {
        $this->response->cache('abc');
    }

    public function testHasCache()
    {
        Reflection::setPropertyValue($this->response, 'boolCache', true);
        $this->assertTrue($this->response->hasCache());
    }
}