<?php
namespace Avenue\Interfaces;

interface RouteInterface
{
    /**
     * Start with the route mapping by accepting the arguments from app route.
     * 
     * @param array $args
     */
    public function init(array $args);
    
    /**
     * Set the particular URI token with a value.
     * 
     * @param mixed $key
     * @param mixed $value
     */
    public function setToken($key, $value);
    
    /**
     * Get the particular token value based on the key.
     * 
     * @param mixed $key
     */
    public function getToken($key);
    
    /**
     * Get all URI tokens in key/value pairs.
     */
    public function getAllTokens();
    
    /**
     * Check if particular route is fulfilled.
     */
    public function fulfilled();
}