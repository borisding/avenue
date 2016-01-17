<?php
use Avenue\App;

use Avenue\Tests\Reflection;

class EncryptionTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $encryption;

    public function setUp()
    {
        $this->app = new App();
        $this->encryption = $this->app->encryption();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyKeyException()
    {
        Reflection::setPropertyValue($this->encryption, 'key', '');
        $this->encryption->set('test');
    }

    public function testEncryptViaSet()
    {
        Reflection::setPropertyValue($this->encryption, 'key', 'mysecretkey');
        Reflection::setPropertyValue($this->encryption, 'cipher', MCRYPT_RIJNDAEL_256);
        Reflection::setPropertyValue($this->encryption, 'mode', MCRYPT_MODE_CBC);
        $this->assertNotEquals('test', $this->encryption->set('test'));
    }

    public function testDecryptViaGet()
    {
        Reflection::setPropertyValue($this->encryption, 'key', 'mysecretkey');
        Reflection::setPropertyValue($this->encryption, 'cipher', MCRYPT_RIJNDAEL_256);
        Reflection::setPropertyValue($this->encryption, 'mode', MCRYPT_MODE_CBC);
        $encryptedData = $this->encryption->set('test');
        $plainText = $this->encryption->get($encryptedData);

        // due to mcrypt_decrypt padding
        // http://www.php.net/manual/en/function.mcrypt-decrypt.php#54734
        $this->assertEquals('test', rtrim($plainText, "\0"));
    }

    public function testGetListOfSupportedCiphers()
    {
        $this->assertTrue(is_array($this->encryption->getSupportedCiphers()));
    }

    public function testGetListOfSupportedModes()
    {
        $this->assertTrue(is_array($this->encryption->getSupportedModes()));
    }
}