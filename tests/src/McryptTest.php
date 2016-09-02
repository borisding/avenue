<?php
use Avenue\App;

use Avenue\Mcrypt;

class McryptTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    private $config;

    public function setUp()
    {
        $this->app = new App();
        $this->config = ['key' => 'mysecret', 'cipher' => MCRYPT_RIJNDAEL_256, 'mode' => MCRYPT_MODE_CBC];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyKeyException()
    {
        $config = ['key' => '', 'cipher' => MCRYPT_RIJNDAEL_256, 'mode' => MCRYPT_MODE_CBC];
        $this->getMcrypt($config)->encrypt('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCipherNotSupportedException()
    {
        $config = ['key' => '', 'cipher' => 'zzz', 'mode' => MCRYPT_MODE_CBC];
        $this->getMcrypt($config)->encrypt('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testModeNotSupportedException()
    {
        $config = ['key' => '', 'cipher' => MCRYPT_RIJNDAEL_256, 'mode' => 'zzz'];
        $this->getMcrypt($config)->encrypt('test');
    }

    public function testEncryptData()
    {
        $this->assertNotEquals('test', $this->getMcrypt()->encrypt('test'));
    }

    public function testDecryptData()
    {
        $mcrypt = $this->getMcrypt();
        // due to mcrypt_decrypt padding
        // http://www.php.net/manual/en/function.mcrypt-decrypt.php#54734
        $encryptedData = $mcrypt->encrypt('test');
        $plainText = $mcrypt->decrypt($encryptedData);
        $this->assertEquals('test', rtrim($plainText, "\0"));
    }

    public function testGetListOfSupportedCiphers()
    {
        $this->assertTrue(is_array($this->getMcrypt()->getSupportedCiphers()));
    }

    public function testGetListOfSupportedModes()
    {
        $this->assertTrue(is_array($this->getMcrypt()->getSupportedModes()));
    }

    private function getMcrypt(array $config = [])
    {
        if (empty($config)) {
            $config = $this->config;
        }

        return new Mcrypt($this->app, $config);
    }
}