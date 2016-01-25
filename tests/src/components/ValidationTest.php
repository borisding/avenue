<?php
namespace Avenue\Tests;

use Avenue\App;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $v;

    public function setUp()
    {
        $this->app = new App();
        $this->v = $this->app->validation();
    }

    public function testInitWithParams()
    {
        $_POST['test'] = 'validation';
        $this->v->init($_POST);

        $value = $this->v->getFields('test');
        $this->assertEquals($_POST['test'], $value);
    }

    public function testInitWithoutParams()
    {
        $_POST['number'] = 123;
        $this->v->init();

        $value = $this->v->getFields('number');
        $this->assertEquals($_POST['number'], $value);
    }

    public function testIsRequiredFailed()
    {
        $_POST['param'] = '';
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsRequiredPassed()
    {
        $_POST['param'] = 'test';
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsRequiredWithNull()
    {
        $_POST['param'] = null;
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsRequiredWithSpaces()
    {
        $_POST['param'] = '     ';
        $this->v->init();
        $this->v->isRequired('param');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testEqualLengthPassed()
    {
        $_POST['author'] = 'Boris Ding Poh Hing';
        $this->v->init();
        $this->v->equalLength('author', 19);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testEqualLengthFailed()
    {
        $_POST['author'] = 'Boris Ding Poh Hing';
        $this->v->init();
        $this->v->equalLength('author', 20);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testMinLengthPassed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->minLength('input', 2);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testMinLengthFailed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->minLength('input', 4);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testMaxLengthPassed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->maxLength('input', 4);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testMaxLengthFailed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->maxLength('input', 2);
        $this->assertFalse($this->v->hasPassed());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInRangeLengthInvalidArgumentException()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->inRangeLength('input', [2, 3, 4]);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testInRangeLengthPassed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->inRangeLength('input', [2, 3]);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testInRangeLengthFailed()
    {
        $_POST['input'] = 'abc';
        $this->v->init();
        $this->v->inRangeLength('input', [1, 2]);
        $this->assertFalse($this->v->hasPassed());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInRangeInvalidArgumentException()
    {
        $_POST['dummy'] = 2;
        $this->v->init();
        $this->v->inRange('dummy', [2, 3, 4]);
        $this->assertFalse($this->v->hasPassed());
    }

    public function testInRangePassed()
    {
        $_POST['dummy'] = 2;
        $this->v->init();
        $this->v->inRange('dummy', [2, 4]);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testInRangeFailed()
    {
        $_POST['dummy'] = 2;
        $this->v->init();
        $this->v->inRange('dummy', [2, 4]);
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsEmailPassed()
    {
        $_POST['email'] = 'foo@bar.com';
        $this->v->init();
        $this->v->isEmail('email');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsEmailFailed()
    {
        $_POST['email'] = 'foo@@bar.com';
        $this->v->init();
        $this->v->isEmail('email');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsUrlPassed()
    {
        $_POST['url'] = 'https://www.github.com';
        $this->v->init();
        $this->v->isUrl('url');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsUrlFailed()
    {
        $_POST['url'] = 'www.github.com';
        $this->v->init();
        $this->v->isUrl('url');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsIpPassed()
    {
        $_POST['ip'] = '127.0.0.1';
        $this->v->init();
        $this->v->isIp('ip');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsIpFailed()
    {
        $_POST['ip'] = '-1.-1.0.0';
        $this->v->init();
        $this->v->isIp('ip');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsScalarPassed()
    {
        $_POST['scalar'] = 'this is string';
        $this->v->init();
        $this->v->isScalar('scalar');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsScalarFailed()
    {
        $_POST['scalar'] = $this->app;
        $this->v->init();
        $this->v->isScalar('scalar');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsFloatPassed()
    {
        $_POST['float'] = '120.0';
        $this->v->init();
        $this->v->isFloat('float');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsFloatFailed()
    {
        $_POST['float'] = '120';
        $this->v->init();
        $this->v->isFloat('float');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsIntegerPassed()
    {
        $_POST['integer'] = '120';
        $this->v->init();
        $this->v->isInteger('integer');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsIntegerFailed()
    {
        $_POST['integer'] = '120.0';
        $this->v->init();
        $this->v->isInteger('integer');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsNumericPassed()
    {
        $_POST['numeric'] = 120;
        $this->v->init();
        $this->v->isNumeric('numeric');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsNumericFailed()
    {
        $_POST['numeric'] = 'abc';
        $this->v->init();
        $this->v->isNumeric('numeric');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsBooleanPassed()
    {
        $_POST['boolean'] = true;
        $this->v->init();
        $this->v->isBoolean('boolean');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsBooleanFailed()
    {
        $_POST['boolean'] = 'false';
        $this->v->init();
        $this->v->isBoolean('boolean');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsDatePassed()
    {
        $_POST['date'] = '2016/01/01';
        $this->v->init();
        $this->v->isDate('date');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsDateFailed()
    {
        $_POST['date'] = 'the day after';
        $this->v->init();
        $this->v->isDate('date');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsDateBeforePassed()
    {
        $_POST['datebefore'] = '2016/01/01';
        $_POST['targetdate'] = '2016/01/15';
        $this->v->init();
        $this->v->isDateBefore('datebefore', 'targetdate');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsDateBeforeFailed()
    {
        $_POST['datebefore'] = '2016/01/01';
        $_POST['targetdate'] = '2015/12/01';
        $this->v->init();
        $this->v->isDateBefore('datebefore', 'targetdate');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsDateAfterPassed()
    {
        $_POST['dateafter'] = '2016/01/31';
        $_POST['targetdate'] = '2016/01/15';
        $this->v->init();
        $this->v->isDateAfter('dateafter', 'targetdate');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsDateAfterFailed()
    {
        $_POST['dateafter'] = '2016/01/01';
        $_POST['targetdate'] = '2016/01/31';
        $this->v->init();
        $this->v->isDateAfter('dateafter', 'targetdate');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsDateEqualPassed()
    {
        $_POST['dateequal'] = '2016/01/15';
        $_POST['targetdate'] = '2016/01/15';
        $this->v->init();
        $this->v->isDateEqual('dateequal', 'targetdate');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsDateEqualFailed()
    {
        $_POST['dateequal'] = '2016/01/01';
        $_POST['targetdate'] = '2016/01/31';
        $this->v->init();
        $this->v->isDateEqual('dateequal', 'targetdate');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsDateAsFormatPassed()
    {
        $_POST['mydate'] = '2016/01/15';
        $this->v->init();
        $this->v->isDateAsFormat('mydate', 'Y/m/d');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsDateAsFormatFailed()
    {
        $_POST['mydate'] = '2016/01/01';
        $this->v->init();
        $this->v->isDateAsFormat('mydate', 'Y-m-d');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testBothEqualPassed()
    {
        $_POST['first'] = 'mytest';
        $_POST['second'] = 'mytest';
        $this->v->init();
        $this->v->bothEqual('first', 'second');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testBothEqualFailed()
    {
        $_POST['first'] = 'mytest';
        $_POST['second'] = 'testme';
        $this->v->init();
        $this->v->bothEqual('first', 'second');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsAlphaPassed()
    {
        $_POST['alpha'] = 'mytest';
        $this->v->init();
        $this->v->isAlpha('alpha');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsAlphaFailed()
    {
        $_POST['alpha'] = '123';
        $this->v->init();
        $this->v->isAlpha('alpha');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsAlnumPassed()
    {
        $_POST['alnum'] = 'mytest123';
        $this->v->init();
        $this->v->isAlnum('alnum');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsAlnumFailed()
    {
        $_POST['alnum'] = false;
        $this->v->init();
        $this->v->isAlnum('alnum');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsDigitPassed()
    {
        $_POST['digit'] = '123';
        $this->v->init();
        $this->v->isDigit('digit');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsDigitFailed()
    {
        $_POST['digit'] = '123.123';
        $this->v->init();
        $this->v->isDigit('digit');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsHexadecimalPassed()
    {
        $_POST['hex'] = 'AB10BC99';
        $this->v->init();
        $this->v->isHexadecimal('hex');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsHexadecimalFailed()
    {
        $_POST['hex'] = 'abx4';
        $this->v->init();
        $this->v->isHexadecimal('hex');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsUpperPassed()
    {
        $_POST['upper'] = 'UPPERCASE';
        $this->v->init();
        $this->v->isUpper('upper');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsUpperFailed()
    {
        $_POST['upper'] = 'NOTuppercase';
        $this->v->init();
        $this->v->isUpper('upper');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsLowerPassed()
    {
        $_POST['lower'] = 'lowercase';
        $this->v->init();
        $this->v->isLower('lower');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsLowerFailed()
    {
        $_POST['lower'] = 'NOTLOWERCASE';
        $this->v->init();
        $this->v->isLower('lower');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsNotWithspacePassed()
    {
        $_POST['whitespace'] = 'abc';
        $this->v->init();
        $this->v->isNotWhitespace('whitespace');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsNotWithspaceFailed()
    {
        $_POST['whitespace'] = '    ';
        $this->v->init();
        $this->v->isNotWhitespace('whitespace');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testIsMatchPassed()
    {
        $_POST['match'] = 'abc';
        $this->v->init();
        $this->v->match('match', '/abc/');
        $this->assertTrue($this->v->hasPassed());
    }

    public function testIsMatchFailed()
    {
        $_POST['match'] = 'PHP';
        $this->v->init();
        $this->v->match('match', '/php/');
        $this->assertFalse($this->v->hasPassed());
    }

    public function testCustomPassed()
    {
        $_POST['custom'] = 'abc';
        $this->v->init();
        $this->v->custom('custom', function() {
            return $this->v->getFields('custom') === 'abc';
        });
        $this->assertTrue($this->v->hasPassed());
    }

    /**
     * @expectedException ErrorException
     */
    public function testIsCustomThrowsException()
    {
        $_POST['custom'] = 'PHP';
        $this->v->init();
        $this->v->custom('custom', 'abc');
        $this->assertFalse($this->v->hasPassed());
    }
}