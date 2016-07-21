<?php
namespace Avenue\Interfaces;

interface McryptInterface
{
    /**
     * Encrypt plain data as provided
     *
     * @param mixed $plaintext
     */
    public function encrypt($data);

    /**
     * Decrypt encrypted data as provided.
     *
     * @param mixed $encrypted
     */
    public function decrypt($data);

    /**
     * Get the configured mcrypt cipher.
     */
    public function getCipher();

    /**
     * Get the configured mcrypt mode.
     */
    public function getMode();

    /**
     * Get the configured key.
     */
    public function getKey();

    /**
     * Get the generated hashed key.
     */
    public function getHashedKey();

    /**
     * Get the list of supported mcrypt cipher algorithms.
     */
    public function getSupportedCiphers();

    /**
     * Get the list of supported mcrypt modes.
     */
    public function getSupportedModes();
}