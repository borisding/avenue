<?php
namespace Avenue\Interfaces;

interface RouteInterface
{
    /**
     * Dispatch route arguments for matching.
     *
     * @param array $args
     */
    public function dispatch(array $args);

    /**
     * Instantiate controller.
     */
    public function initController();

    /**
     * Build controller class namespace.
     */
    public function buildControllerNamespace();

    /**
     * Match the route URI with regular expression.
     */
    public function matchRoute();

    /**
     * Set respective route params with actual value.
     */
    public function setRouteParams();

    /**
     * Set the default values route parameters.
     */
    public function setDefaultRouteParams();

    /**
     * Mapping the resource method representation.
     */
    public function mapResourceMethod();

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

    /**
     * Get the actual matched rule's regexp.
     */
    public function getMatchedRuleRegexp();
}
