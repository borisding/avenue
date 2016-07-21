<?php
use Avenue\App;

use Avenue\Tests\Reflection;

class McryptTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $mcrypt;

    public function setUp()
    {
        $this->app = new App();
        Reflection::setPropertyValue($this->app, 'config', ['timezone' => 'UTC', 'encryption' =>[]]);
        $this->mcrypt = $this->app->mcrypt();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyKeyException()
    {
        Reflection::setPropertyValue($this->mcrypt, 'config', ['key' => '', 'cipher' => MCRYPT_RIJNDAEL_256, 'mode' => MCRYPT_MODE_CBC]);
        $this->mcrypt->encrypt('test');
    }

    public function testEncryptData()
    {
        Reflection::setPropertyValue($this->mcrypt, 'config', ['key' => 'mysecret', 'cipher' => MCRYPT_RIJNDAEL_256, 'mode' => MCRYPT_MODE_CBC]);
        $this->assertNotEquals('test', $this->mcrypt->encrypt('test'));
    }

    public function testDecryptData()
    {
        Reflection::setPropertyValue($this->mcrypt, 'config', ['key' => 'mysecret', 'cipher' => MCRYPT_RIJNDAEL_256, 'mode' => MCRYPT_MODE_CBC]);
        // due to mcrypt_decrypt padding
        // http://www.php.net/manual/en/function.mcrypt-decrypt.php#54734
        $encryptedData = $this->mcrypt->encrypt('test');
        $plainText = $this->mcrypt->decrypt($encryptedData);
        $this->assertEquals('test', rtrim($plainText, "\0"));
    }

    public function testGetListOfSupportedCiphers()
    {
        $this->assertTrue(is_array($this->mcrypt->getSupportedCiphers()));
    }

    public function testGetListOfSupportedModes()
    {
        $this->assertTrue(is_array($this->mcrypt->getSupportedModes()));
    }
}