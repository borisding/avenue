<?php
namespace Avenue\Tests;

abstract class Reflection
{
    public static function getClassMethod($obj, $method, array $params = [])
    {
        $rc = new \ReflectionClass(get_class($obj));
        $cm = $rc->getMethod($method);
        $cm->setAccessible(true);

        return $cm->invokeArgs($obj, $params);
    }
}