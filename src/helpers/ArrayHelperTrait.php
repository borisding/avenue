<?php
namespace Avenue\Helpers;

trait ArrayHelperTrait
{
    /**
     * Shortcut of returning array element based on name.
     * Return default if element not found.
     *
     * @param mixed $name
     * @param array $arr
     * @param string $default
     * @return mixed
     */
    public function arrGet($name, array $arr = [], $default = null)
    {
        return isset($arr[$name]) ? $arr[$name] : $default;
    }

    /**
     * Check whether provided data is associative array.
     * Return true if it is valid.
     *
     * @param mixed $data
     * @return boolean
     */
    public function arrIsAssoc($data)
    {
        if (!is_array($data)) {
            return false;
        }

        $keys = array_keys($data);
        return $keys !== array_keys($keys);
    }

    /**
     * Check whether provided data is index based array.
     * Return true if it is valid.
     *
     * @param mixed $data
     * @return boolean
     */
    public function arrIsIndex($data)
    {
        if (!is_array($data)) {
            return false;
        }

        return array_values($data) === $data;
    }

    /**
     * Return associative array's first key.
     *
     * @param array $arr
     * @return NULL|unknown
     */
    public function arrFirstKey(array $arr)
    {
        if (empty($arr)) {
            return null;
        }

        $arrKeys = array_keys($arr);
        return $arrKeys[0];
    }

    /**
     * Removed empty element from numeric array.
     * Elements will be re-indexed after removal.
     *
     * @param array $arr
     */
    public function arrRemoveEmpty(array $arr)
    {
        return array_values(array_filter($arr));
    }

    /**
     * Shortcut of converting array to JSON format.
     *
     * @param array $arr
     * @param string $valueOnly
     */
    public function arrToJson(array $arr = [], $valueOnly = false)
    {
        if ($valueOnly) {
            $arr = array_values($arr);
        }

        return json_encode($arr);
    }
}