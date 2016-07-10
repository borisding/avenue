<?php
namespace Avenue\Interfaces;

interface RouteInterface
{
    /**
     * Start route mapping based on the provided arguments.
     *
     * @param array $args
     */
    public function init(array $args);

    /**
     * Set value for particular URI based on the key/value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function setParam($key, $value);

    /**
     * Get the route parameter value based on the key.
     *
     * @param mixed $key
     */
    public function getParams($key = null);

    /**
     * Check if particular route rule is fulfilled.
     */
    public function isFulfilled();
}