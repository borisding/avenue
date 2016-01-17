<?php
namespace Avenue\Tests;

abstract class Reflection
{
    /**
     * Protected/private methods accessible helper.
     * Call class method by assigning params.
     *
     * @param object $obj
     * @param mixed $method
     * @param array $params
     */
    public static function callClassMethod($obj, $method, array $params = [])
    {
        $rc = new \ReflectionClass(get_class($obj));
        $cm = $rc->getMethod($method);
        $cm->setAccessible(true);

        return $cm->invokeArgs($obj, $params);
    }

    /**
     * Protected/private property accessible helper.
     * Assign expected value for testing.
     *
     * @param object $obj
     * @param mixed $property
     * @param mixed $value
     * @param string $static
     */
    public static function setPropertyValue($obj, $property, $value, $static = false)
    {
        $ro = new \ReflectionObject($obj);
        $p = $ro->getProperty($property);
        $p->setAccessible(true);

        if ($static) {
            $p->setValue(null, $value);
        } else {
            $p->setValue($obj, $value);
        }
    }

    /**
     * Get class constant value.
     *
     * @param mixed $obj
     * @param mixed $constant
     */
    public static function getConstantValue($obj, $constant)
    {
        $ro = new \ReflectionClass(get_class($obj));
        return $ro->getConstant($constant);
    }
}