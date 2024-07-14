<?php declare(strict_types=1);
namespace RainCity\TestHelper;

class ReflectionHelper
{
    /**
     * Fetch the value of a class property.
     *
     * @param string $clazz The name of the class, typically provided as
     *      "Classname::class"
     * @param string $prop The name of the property to retrieve.
     *
     * @return mixed The value of the property. Note: If the value passed for
     *      $clazz is not actually a class then null is returned.
     */
    public static function getClassProperty(string $clazz, string $prop)
    {
        return self::getObjectProperty($clazz, $prop, null);
    }

    /**
     * Fetch the value of a object property.
     *
     * @param string $clazz The name of the class, typically provided as
     *      "Classname::class"
     * @param string $prop The name of the property to retrieve.
     * @param object|NULL $obj The object to retrieve the value from. If
     *      passed as null assumes the property is a class property.
     *
     * @return mixed The value of the property. Null is returned if value
     *      passed for $clazz is not actually a class or the property doesn't
     *      exist.
     */
    public static function getObjectProperty(string $clazz, string $prop, ?object $obj)
    {
        $result = null;

        if (class_exists($clazz)) {
            $reflectionClass = (new \ReflectionClass($clazz));

            if ($reflectionClass->hasProperty($prop)) {
                $reflectionProp = $reflectionClass->getProperty($prop);

                if ($reflectionProp->isPrivate() || $reflectionProp->isProtected()) {
                    $reflectionProp->setAccessible(true);
                    $result = $reflectionProp->getValue($obj);
                    $reflectionProp->setAccessible(false);
                } else {
                    $result = $reflectionProp->getValue($obj);
                }
            } else {
                $parentClass = $reflectionClass->getParentClass();
                if ($parentClass) {
                    $result = static::getObjectProperty($parentClass->getName(), $prop, $obj);
                }
            }
        }

        return $result;
    }

    /**
     * Set the value of a class property.
     *
     * @param string $clazz The name of the class, typically provided as
     *      "Classname::class"
     * @param string $prop The name of the property to set.
     * @param mixed $value The value to set for the property.
     */
    public static function setClassProperty(string $clazz, string $prop, $value)
    {
        self::setObjectProperty($clazz, $prop, $value, null);
    }

    /**
     * Set the value of a object property.
     *
     * @param string $clazz The name of the class, typically provided as
     *      "Classname::class"
     * @param string $prop The name of the property to set.
     * @param mixed $value The value to set for the property.
     * @param object|NULL $obj The object to retrieve the value from. If
     *      passed as null assumes the property is a class property.
     */
    public static function setObjectProperty(string $clazz, string $prop, $value, ?object $obj)
    {
        if (class_exists($clazz)) {
            $reflectionClass = (new \ReflectionClass($clazz));

            if ($reflectionClass->hasProperty($prop)) {
                $reflectionProp = $reflectionClass->getProperty($prop);

                if ($reflectionProp->isPrivate() || $reflectionProp->isProtected()) {
                    $reflectionProp->setAccessible(true);
                    $value = $reflectionProp->setValue($obj, $value);
                    $reflectionProp->setAccessible(false);
                } else {
                    $value = $reflectionProp->setValue($obj, $value);
                }
            } else {
                $parentClass = $reflectionClass->getParentClass();
                if ($parentClass) {
                    static::setObjectProperty($parentClass->getName(), $prop, $value, $obj);
                }
            }
        }
    }

    /**
     * Invoke an method on an object.
     *
     * @param string $clazz The name of the class, typically provided as
     *      "Classname::class"
     * @param object $obj The object to retrieve the value from.
     * @param string $methodName The name of the method to invoke.
     * @param mixed ...$args Zero or more arguments to pass to the method.
     *
     * @return mixed The value returned by the method.
     */
    public static function invokeObjectMethod(string $clazz, object $obj, string $methodName, ...$args)
    {
        $result = null;

        if (class_exists($clazz)) {
            $reflection = new \ReflectionClass($clazz);

            if ($reflection->hasMethod($methodName)) {
                $method = $reflection->getMethod($methodName);

                if ($method->isPrivate() || $method->isProtected()) {
                    $method->setAccessible(true);
                    $result = $method->invokeArgs($obj, $args);
                    $method->setAccessible(false);
                } else {
                    $result = $method->invokeArgs($obj, $args);
                }
            } else {
                $parentClass = $reflection->getParentClass();
                if ($parentClass) {
                    $result = static::invokeObjectMethod($parentClass->getName(), $obj, $methodName, $args);
                }
            }
        }

        return $result;
    }
}

