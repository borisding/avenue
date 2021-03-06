<?php
namespace Avenue\Tests\Helpers;

use Avenue\App;

class HelperBundleTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new App(['timezone' => 'UTC'], uniqid(rand()));
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

    public function testRepeatFillString()
    {
        $result = $this->app->fillRepeat('?', ',', 0, 2);
        $this->assertEquals('?,?', $result);
    }

    public function testArrRemoveEmptyElement()
    {
        $arr = ['a', 'b', '', 'd', null, 'f'];
        $result = $this->app->arrRemoveEmpty($arr);
        $this->assertEquals(4, count($result));
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
        $input = 'abc123';
        $this->assertTrue($this->app->isAlnum($input));
    }

    public function testInputAlnumIsFalse()
    {
        $input = 'abc123_-~!@';
        $this->assertFalse($this->app->isAlnum($input));
    }

    public function testInputAlphaIsTrue()
    {
        $input = 'abcABC';
        $this->assertTrue($this->app->isAlpha($input));
    }

    public function testInputAlphaIsFalse()
    {
        $input = 'abc123_~!@';
        $this->assertFalse($this->app->isAlpha($input));
    }

    public function testInputDigitIsTrue()
    {
        $input = '12345';
        $this->assertTrue($this->app->isDigit($input));
    }

    public function testInputDigitIsFalse()
    {
        $input = 'abc123_~!@';
        $this->assertFalse($this->app->isDigit($input));
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

    public function testIsValidMethodName()
    {
        $methodName1 = 'abc';
        $this->assertTrue($this->app->isValidMethodName($methodName1));

        $methodName2 = 'ABC';
        $this->assertTrue($this->app->isValidMethodName($methodName2));

        $methodName3 = 'abcABC';
        $this->assertTrue($this->app->isValidMethodName($methodName3));

        $methodName4 = 'abcABC_123';
        $this->assertTrue($this->app->isValidMethodName($methodName4));

        $methodName5 = '_abc_ABC_123';
        $this->assertTrue($this->app->isValidMethodName($methodName5));
    }

    public function testIsInvalidValidMethodName()
    {
        $methodName1 = 'abc!@#';
        $this->assertFalse($this->app->isValidMethodName($methodName1));

        $methodName2 = '~!@ABC';
        $this->assertFalse($this->app->isValidMethodName($methodName2));

        $methodName3 = '123abcABC';
        $this->assertFalse($this->app->isValidMethodName($methodName3));

        $methodName4 = 'abcABC_123_!@#';
        $this->assertFalse($this->app->isValidMethodName($methodName4));

        $methodName5 = '_abc-ABC-123';
        $this->assertFalse($this->app->isValidMethodName($methodName5));
    }
}
