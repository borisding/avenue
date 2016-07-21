<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\McryptInterface;

class Mcrypt implements McryptInterface
{
    /**
     * App class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * Default encryption configuration.
     *
     * @var array
     */
    protected $config = [
        'key' => '',
        'cipher' => MCRYPT_RIJNDAEL_256,
        'mode' => MCRYPT_MODE_CBC
    ];

    /**
     * Mcrypt class constructor.
     *
     * @param App $app
     * @param array $config
     */
    public function __construct(App $app, array $config = [])
    {
        $this->app = $app;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Encrypt method for encrypting plain $data.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::encrypt($data)
     */
    public function encrypt($data)
    {
        $key = $this->getHashedKey();
        $cipher = $this->getCipher();
        $mode = $this->getMode();

        $ivSize = mcrypt_get_iv_size($cipher, $mode);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);
        $encrypted = $iv.mcrypt_encrypt($cipher, $key, $data, $mode, $iv);

        return base64_encode($encrypted);
    }

    /**
     * Decrypt method for decrypting encrypted data.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::decrypt($data)
     */
    public function decrypt($data)
    {
        try {
            $data = base64_decode($data, true);
            $key = $this->getHashedKey();
            $cipher = $this->getCipher();
            $mode = $this->getMode();

            $ivSize = mcrypt_get_iv_size($cipher, $mode);
            $iv = substr($data, 0, $ivSize);
            $data = substr($data, $ivSize);

            return mcrypt_decrypt($cipher, $key, $data, $mode, $iv);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get the configured mcrypt cipher.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::getCipher()
     */
    public function getCipher()
    {
        if (!in_array($this->config['cipher'], $this->getSupportedCiphers())) {
            throw new \InvalidArgumentException('Mcrypt algorithm is not supported!');
        }

        return $this->config['cipher'];
    }

    /**
     * Get the configured mcrypt mode.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::getMode()
     */
    public function getMode()
    {
        if (!in_array($this->config['mode'], $this->getSupportedModes())) {
            throw new \InvalidArgumentException('Mcrypt mode is not supported!');
        }

        return $this->config['mode'];
    }

    /**
     * Get the configured key.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::getKey()
     */
    public function getKey()
    {
        return $this->config['key'];
    }

    /**
     * Get the generated hashed key.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::getHashedKey()
     */
    public function getHashedKey()
    {
        $key = $this->getKey();
        $cipher = $this->getCipher();
        $mode = $this->getMode();

        if (empty(trim($key))) {
            throw new \InvalidArgumentException('Key must NOT be empty!');
        }

        $keySize = mcrypt_get_key_size($cipher, $mode);
        $hashedKey = hash('sha256', $key);

        return substr($hashedKey, 0, $keySize);
    }

    /**
     * Get the list of supported mcrypt cipher algorithms.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::getSupportedCiphers()
     */
    public function getSupportedCiphers()
    {
        return mcrypt_list_algorithms();
    }

    /**
     * Get the list of supported mcrypt modes.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\McryptInterface::getSupportedModes()
     */
    public function getSupportedModes()
    {
        return mcrypt_list_modes();
    }
}