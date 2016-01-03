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
     * Check whether array is associative array.
     * Return true if it is valid.
     *
     * @param array $arr
     * @return boolean
     */
    public function arrIsAssoc(array $arr)
    {
        $keys = array_keys($arr);
        return $keys !== array_keys($keys);
    }
    
    /**
     * Check whether array is index based.
     * Return true if it is valid.
     *
     * @param array $arr
     * @return boolean
     */
    public function arrIsIndex(array $arr)
    {
        return array_values($arr) === $arr;
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
     * Fill array with a value based on start and end index.
     * Then join it with passed in delimiter as a string.
     * 
     * @param mixed $delimiter
     * @param array $value
     * @param mixed $start
     * @param mixed $end
     */
    public function arrFillJoin($delimiter, $value, $start, $end)
    {
        return implode($delimiter, array_fill($start, $end, $value));
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