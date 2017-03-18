<?php
namespace Avenue\Interfaces\State;

interface SessionInterface
{
    /**
     * Set session value with provided key/value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value);

    /**
     * Get session value based on the key.
     *
     * @param mixed $key
     */
    public function get($key);

    /**
     * Remove session based on the provided key.
     *
     * @param mixed $key
     */
    public function remove($key);

    /**
     * Remove all session values.
     */
    public function removeAll();

    /**
     * Regenerate new session identifier (ID).
     */
    public function regenerateId();

    /**
     * Get session identifier (ID).
     */
    public function getId();

    /**
     * Start session.
     */
    public function start();

    /**
     * Set session for CSRF token.
     */
    public function setCsrfToken();

    /**
     * Get CSRF token value.
     */
    public function getCsrfToken();
}
