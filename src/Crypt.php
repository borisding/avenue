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
    protected $secretKey;

    /**
     * Crypt class constructor.
     *
     * @param mixed $secretKey
     * @throws \InvalidArgumentException
     */
    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;

        if (empty(trim($this->secretKey))) {
            throw new \InvalidArgumentException('Secret key must not be empty!');
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
        $salt = openssl_random_pseudo_bytes(16);
        $key = $this->generateKey($salt);

        $iv = $this->generateVector($salt);
        $cipher = $this->getCipher();

        return base64_encode($salt . openssl_encrypt($data, $cipher, $key, 1, $iv));
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
        $salt = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);

        $key = $this->generateKey($salt);
        $iv = $this->generateVector($salt);
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
     * Generate 16-bytes vector with pseudo-random salt bytes in raw output.
     *
     * @param string $salt
     * @return string
     */
    protected function generateVector($salt)
    {
        return md5($salt, true);
    }

    /**
     * Generate 32-bytes key with secret key and pseudo-random salt bytes in raw output.
     *
     * @param string $salt
     * @return string
     */
    protected function generateKey($salt)
    {
        return hash_hmac('sha256', $this->secretKey . '~~~' . $salt, $salt, true);
    }
}