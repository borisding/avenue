<?php
namespace Avenue;

use Avenue\Interfaces\CryptInterface;

class Crypt implements CryptInterface
{
    /**
     * Targeted cipher to use for AES-256 encryption.
     *
     * @var string
     */
    const CIPHER = 'AES-256-CBC';

    /**
     * Targeted length to use for salt.
     *
     * @var integer
     */
    const LENGTH = 16;

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
        $salt = openssl_random_pseudo_bytes(static::LENGTH);
        $hashed = $this->generateHashed($salt);

        $key = $this->generateKey($hashed);
        $iv = $this->generateVector($salt);

        return base64_encode($salt . openssl_encrypt($data, static::CIPHER, $key, 1, $iv));
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
        $salt = substr($decoded, 0, static::LENGTH);
        $encrypted = substr($decoded, strlen($salt));

        $hashed = $this->generateHashed($salt);
        $key = $this->generateKey($hashed);
        $iv = $this->generateVector($salt);

        return openssl_decrypt($encrypted, static::CIPHER, $key, 1, $iv);
    }

    /**
     * Generate 32-bytes key with reversed hashed string.
     *
     * @param string $hashed
     * @return string
     */
    protected function generateKey($hashed)
    {
        return strrev($hashed);
    }

    /**
     * Generate 16-bytes vector with reversed salt string in raw output.
     *
     * @param string $salt
     * @return string
     */
    protected function generateVector($salt)
    {
        return md5(strrev($salt), true);
    }

    /**
     * Generate hashed string with secret key and salt in raw output.
     *
     * @param string $salt
     * @return string
     */
    protected function generateHashed($salt)
    {
        return hash_hmac('sha256', $this->secretKey . '~~~' . $salt, $salt, true);
    }
}