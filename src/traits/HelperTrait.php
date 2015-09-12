<?php
namespace Avenue\Traits;

trait HelperTrait
{
    /**
     * Shortcut of returning array element based on name.
     * Return default if element not found.
     *
     * @param unknown $name
     * @param array $arr
     * @param string $default
     * @return mixed
     */
    public function arrGet($name, array $arr = [], $default = null)
    {
        return isset($arr[$name]) ? $arr[$name] : $default;
    }
}