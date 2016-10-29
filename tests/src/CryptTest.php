<?php
use Avenue\Crypt;

class CryptTest extends \PHPUnit_Framework_TestCase
{
    private $secret = 'mysecretfortesting';

    private $plaintext = 'borisding';

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptySecretException()
    {
        $crypt = new Crypt('');
    }

    public function testEncryptData()
    {
        $crypt = new Crypt($this->secret);
        $encryptedData = $crypt->encrypt($this->plaintext);
        $this->assertNotEquals($this->plaintext, $encryptedData);
    }

    public function testDecryptData()
    {
        $crypt = new Crypt($this->secret);
        $encryptedData = $crypt->encrypt($this->plaintext);
        $decryptedData = $crypt->decrypt($encryptedData);

        $this->assertEquals($this->plaintext, $decryptedData);
    }
}