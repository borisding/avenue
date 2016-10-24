<?php
namespace Avenue\Interfaces;

interface CryptInterface
{
    /**
     * Perform encryption for provided plain data.
     *
     * @param mixed $data
     */
    public function encrypt($data);

    /**
     * Perform decryption for provided encrypted data.
     *
     * @param mixed $data
     */
    public function decrypt($data);
}