<?php
namespace Avenue\Tests;

use Avenue\App;

class HelperBundleTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App();
    }

    public function testArrGetIsNull()
    {
        $emptyArray = [];
        $testValue = $this->app->arrGet('test', $emptyArray);
        $this->assertNull($testValue);
    }

    public function testArrGetIsEmpty()
    {
        $emptyArray = [];
        $testValue = $this->app->arrGet('test', $emptyArray, '');
        $this->assertEmpty($testValue);
    }

    public function testArrGetDefaultValue()
    {
        $emptyArray = [];
        $defaultValue = 'iamdefault';
        $testValue = $this->app->arrGet('test', $emptyArray, $defaultValue);
        $this->assertEquals($defaultValue, $testValue);
    }

    public function testArrIsAssocTrue()
    {
        $arr = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $this->assertTrue($this->app->arrIsAssoc($arr));
    }

    public function testArrIsAssocFalse()
    {
        $arr = ['value1', 'value2'];
        $this->assertFalse($this->app->arrIsAssoc($arr));
    }

    public function testArrIsIndexTrue()
    {
        $arr = ['value1', 'value2'];
        $this->assertTrue($this->app->arrIsIndex($arr));
    }

    public function testArrIsIndexFalse()
    {
        $arr = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $this->assertFalse($this->app->arrIsIndex($arr));
    }

    public function testArrFirstKeyIsNull()
    {
        $emptyArray = [];
        $testValue = $this->app->arrFirstKey($emptyArray);
        $this->assertNull($testValue);
    }

    public function testGetArrFirstKey()
    {
        $arr = [
            'foo' => 'bar',
            'hello' => 'world'
        ];
        $firstKey = $this->app->arrFirstKey($arr);
        $this->assertEquals('foo', $firstKey);
    }

    public function testArrFillJoinWithDelimiter()
    {
        $result = $this->app->arrFillJoin(',', '?', 0, 2);
        $this->assertEquals('?,?', $result);
    }

    public function testArrRemoveEmptyElement()
    {
        $arr = ['a', 'b', '', 'd', null, 'f'];
        $result = $this->app->arrRemoveEmpty($arr);
        $this->assertEquals(4, count($result));
    }

    public function testArrayIndexedToJson()
    {
        $arr = ['a', 'b', 'c'];
        $jsonData = $this->app->arrToJson($arr);
        $this->assertEquals('["a","b","c"]', $jsonData);
    }

    public function testArrayAssocToJson()
    {
        $arr = [
            'foo' => 'bar',
            'hello' => 'world'
        ];
        $jsonData = $this->app->arrToJson($arr);
        $this->assertEquals('{"foo":"bar","hello":"world"}', $jsonData);
    }

    public function testArrayAssocToJsonWithValueOnly()
    {
        $arr = [
            'foo' => 'bar',
            'hello' => 'world'
        ];
        $jsonData = $this->app->arrToJson($arr, true);
        $this->assertEquals('["bar","world"]', $jsonData);
    }

    public function testJsonToArr()
    {
        $jsonData = '{
           "test": 123,
           "hello": "world"
        }';

        $arr = $this->app->jsonToArr($jsonData);
        $this->assertEquals(['test' => 123, 'hello' => 'world'], $arr);
    }

    public function testJsonToObj()
    {
        $jsonData = '{
           "test": 123,
           "hello": "world"
        }';

        $jsonObj = $this->app->jsonToObj($jsonData);
        $this->assertEquals('123', $jsonObj->test);
    }

    public function testEscapeInputString()
    {
        $input = "<script>alert('');</script>";
        $result = $this->app->escape($input);
        $this->assertEquals("&lt;script&gt;alert('');&lt;/script&gt;", $result);
    }

    public function testEscapeUnicodeInputString()
    {
        $input = "<script>alert('你好吗');</script>";
        $result = $this->app->escape($input);
        $this->assertEquals("&lt;script&gt;alert('你好吗');&lt;/script&gt;", $result);
    }

    public function testEscapeAllQuotesForInputString()
    {
        $input = "<script>alert('');</script>";
        $result = $this->app->escape($input, ENT_QUOTES);
        $this->assertEquals('&lt;script&gt;alert(&#039;&#039;);&lt;/script&gt;', $result);
    }

    public function testEscapeInputStringExceptExistingHtmlEntities()
    {
        $input = "&amp;thisstringwithampersand<>";
        $result = $this->app->escape($input, ENT_COMPAT, 'UTF-8', false);
        $this->assertEquals('&amp;thisstringwithampersand&lt;&gt;', $result);
    }

    public function testEscapeAliasMethod()
    {
        $input = "<script>alert('');</script>";
        $result = $this->app->e($input);
        $this->assertEquals("&lt;script&gt;alert('');&lt;/script&gt;", $result);
    }

    public function testEscapeEachArrayIndexedElement()
    {
        $input = ['<>', '&'];
        $result = $this->app->escapeEach($input);
        $this->assertEquals(['&lt;&gt;', '&amp;'], $result);
    }

    public function testEscapeEachArrayAsscValue()
    {
        $input = [
            'key1' => '<>',
            'key2' => '&'
        ];
        $result = $this->app->escapeEach($input);
        $this->assertEquals(['key1' => '&lt;&gt;', 'key2' => '&amp;'], $result);
    }

    public function testEscapeEachArrayIndexedElementWithAliasMethod()
    {
        $input = ['<>', '&'];
        $result = $this->app->ee($input);
        $this->assertEquals(['&lt;&gt;', '&amp;'], $result);
    }

    public function testInputAlnumIsTrue()
    {
        $input = 'abc123_';
        $this->assertTrue($this->app->isInputAlnum($input));
    }

    public function testInputAlnumIsFalse()
    {
        $input = 'abc123_~!@';
        $this->assertFalse($this->app->isInputAlnum($input));
    }

    public function testMatchHashedStringIsTrue()
    {
        $secret = 'foo-bar';
        $md5Hashed = hash_hmac('md5', '123', $secret);
        $this->assertTrue($this->app->hashedCompare('8352444af31e496ec5f66846c47ee305', $md5Hashed));
    }

    public function testMatchHashedStringIsFalse()
    {
        $secret = 'hello-world';
        $md5Hashed = hash_hmac('md5', '123', $secret);
        $this->assertFalse($this->app->hashedCompare('8352444af31e496ec5f66846c47ee305', $md5Hashed));
    }
}