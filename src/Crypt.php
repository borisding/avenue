<?php
namespace Avenue;

use Avenue\Interfaces\CryptInterface;

class Crypt implements CryptInterface
{
    /**
     * Targeted OpenSSL AES-256 cipher to use for encryption.
     *
     * @var string
     */
    const AES256_CIPHER = 'AES-256-CBC';

    /**
     * App's secret key.
     *
     * @var string
     */
    protected $secret;

    /**
     * Crypt class constructor.
     *
     * @param mixed $secret
     * @throws \InvalidArgumentException
     */
    public function __construct($secret)
    {
        $this->secret = $secret;

        if (empty(trim($this->secret))) {
            throw new \InvalidArgumentException('Secret must not be empty!');
        }
    }

    /**
     * Encrypt plain data.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\CryptInterface::encrypt()
     */
    public function encrypt($data)
    {
        $salt01 = openssl_random_pseudo_bytes(16);
        $salt02 = openssl_random_pseudo_bytes(16);

        $key = $this->generateKey($salt01 . $salt02);
        $iv = $this->generateVector($salt01 . $salt02);
        $cipher = $this->getCipher();

        return base64_encode($salt01 . openssl_encrypt($data, $cipher, $key, 1, $iv) . $salt02);
    }

    /**
     * Decrypt ciphered data.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\CryptInterface::decrypt()
     */
    public function decrypt($data)
    {
        $decoded = base64_decode($data);
        $salt01 = substr($decoded, 0, 16);
        $salt02 = substr($decoded, -16);
        $encrypted = substr($decoded, 16, -16);

        $key = $this->generateKey($salt01 . $salt02);
        $iv = $this->generateVector($salt01 . $salt02);
        $cipher = $this->getCipher();

        return openssl_decrypt($encrypted, $cipher, $key, 1, $iv);
    }

    /**
     * Get the targeted AES-256 cipher.
     *
     * @return string
     */
    protected function getCipher()
    {
        return static::AES256_CIPHER;
    }

    /**
     * Generate 16-byte vector with pseudo-random salt bytes in raw output.
     *
     * @param mixed $concatSalt
     * @return string
     */
    protected function generateVector($concatSalt)
    {
        return md5($concatSalt, true);
    }

    /**
     * Generate 32-byte key with secret key and pseudo-random salt bytes in raw output.
     *
     * @param mixed $concatSalt
     * @return string
     */
    protected function generateKey($concatSalt)
    {
        return hash_hmac('sha256', $this->secret . $concatSalt, $concatSalt, true);
    }
}