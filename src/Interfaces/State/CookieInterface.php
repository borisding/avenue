<?php
namespace Avenue\Interfaces\State;

interface CookieInterface
{
    /**
     * Set cookie value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value);

    /**
     * Get cookie value.
     *
     * @param mixed $key
     */
    public function get($key);

    /**
     * Remove cookie value.
     *
     * @param mixed $key
     */
    public function remove($key);

    /**
     * Remove all cookie values.
     */
    public function removeAll();

    /**
     * Return cookie specific config based on the name.
     *
     * @param mixed $name
     */
    public function getConfig($name);
}
