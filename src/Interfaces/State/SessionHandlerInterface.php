<?php
namespace Avenue\Interfaces\State;

interface SessionHandlerInterface
{
    /**
     * Get the apps' secret.
     */
    public function getAppSecret();

    /**
     * Get session config based on the name.
     *
     * @param mixed $name
     */
    public function getConfig($name);
}
