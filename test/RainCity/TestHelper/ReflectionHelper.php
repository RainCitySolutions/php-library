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
     * @param boolean $useParent A flag indicating that the property is
     *      actually on a parent class.
     *
     * @return mixed The value of the property. Note: If the value passed for
     *      $clazz is not actually a class then null is returned.
     */
    public static function getClassProperty(string $clazz, string $prop, bool $useParent = false) {
        return self::getObjectProperty($clazz, $prop, null, $useParent);
    }

    /**
     * Fetch the value of a object property.
     *
     * @param string $clazz The name of the class, typically provided as
     *      "Classname::class"
     * @param string $prop The name of the property to retrieve.
     * @param object|NULL $obj The object to retrieve the value from. If
     *      passed as null assumes the property is a class property.
     * @param boolean $useParent A flag indicating that the property is
     *      actually on a parent class.
     *
     * @return mixed The value of the property. Note: If the value passed for
     *      $clazz is not actually a class then null is returned.
     */
    public static function getObjectProperty(string $clazz, string $prop, ?object $obj, bool $useParent = false) {
        $result = null;

        if (class_exists($clazz)) {
            $reflectionClass = (new \ReflectionClass($clazz));

            if ($reflectionClass->hasProperty($prop)) {
/*
                if ($useParent) {
                    $parentClass = $reflectionClass->getParentClass();
                    if ($parentClass) {
                        $reflectionClass = $parentClass;
                    }
                }
*/
                $reflectionProp = $reflectionClass->getProperty($prop);

                if ($reflectionProp->isPrivate() || $reflectionProp->isProtected()) {
                    $needAccess = true;
                }
                else {
                    $needAccess = false;
                }

                if ($needAccess) {
                    $reflectionProp->setAccessible(true);
                }

                $result = $reflectionProp->getValue($obj);

                if ($needAccess) {
                    $reflectionProp->setAccessible(false);
                }
            }
            else {
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
     * @param boolean $useParent A flag indicating that the property is
     *      actually on a parent class.
     */
    public static function setClassProperty(string $clazz, string $prop, $value, $useParent = false) {
        self::setObjectProperty($clazz, $prop, $value, null, $useParent);
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
     * @param boolean $useParent A flag indicating that the property is
     *      actually on a parent class.
     */
    public static function setObjectProperty(string $clazz, string $prop, $value, ?object $obj, $useParent = false) {
        if (class_exists($clazz)) {
            $reflectionClass = (new \ReflectionClass($clazz));

/*
            if ($useParent) {
                $parentClass = $reflectionClass->getParentClass();
                if ($parentClass) {
                    $reflectionClass = $parentClass;
                }
            }
*/
            if ($reflectionClass->hasProperty($prop)) {
                $reflectionProp = $reflectionClass->getProperty($prop);

                if ($reflectionProp->isPrivate() || $reflectionProp->isProtected()) {
                    $needAccess = true;
                }
                else {
                    $needAccess = false;
                }

                if ($needAccess) {
                    $reflectionProp->setAccessible(true);
                }

                $value = $reflectionProp->setValue($obj, $value);

                if ($needAccess) {
                    $reflectionProp->setAccessible(false);
                }
            }
            else {
                $parentClass = $reflectionClass->getParentClass();
                if ($parentClass) {
                    static::setObjectProperty($parentClass->getName(), $prop, $value, $obj);
                }
            }
        }
    }

    /**
     * Compare two arrays to see if they are identical.
     *
     * @param array $srcArray
     * @param array $tgtArray
     *
     * @return bool
     */
    public static function arraysTheSame(array $srcArray, array $tgtArray): bool {
        $same = true;

        if (count($srcArray) === count($tgtArray)) {
            foreach ($srcArray as $key => $srcEntry) {
                if (array_key_exists($key, $tgtArray)) {
                    if (is_array($srcEntry) && is_array($tgtArray[$key])) {
                        if (!self::arraysTheSame($srcEntry, $tgtArray[$key])) {
                            $same = false;
                            break;
                        }
                    }
                    else {
                        if ($srcEntry != $tgtArray[$key]) {
                            $same = false;
                            break;
                        }
                    }
                }
                else {
                    $same = false;
                    break;
                }
            }
        }
        else {
            $same = false;
        }

        return $same;
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
    public static function invokeObjectMethod(string $clazz, object $obj, string $methodName, ...$args) {
        $result = null;

        if (class_exists($clazz)) {
            $reflection = new \ReflectionClass($clazz);

            if ($reflection->hasMethod($methodName)) {
                $method = $reflection->getMethod($methodName);

                if ($method->isPrivate() || $method->isProtected()) {
                    $needAccess = true;
                }
                else {
                    $needAccess = false;
                }

                if ($needAccess) {
                    $method->setAccessible(true);
                }

                $result = $method->invokeArgs($obj, $args);

                if ($needAccess) {
                    $method->setAccessible(false);
                }

                $method->setAccessible(true);
            }
            else {
                $parentClass = $reflection->getParentClass();
                if ($parentClass) {
                    $result = static::invokeObjectMethod($parentClass->getName(), $obj, $methodName, $args);
                }
            }
        }

        return $result;
    }
}

/**
function getSetClassProperty ($clazz, $obj, $prop, $value = null, $useParent = false)
{
    $retValue = null;

    $reflectionClass = new \ReflectionClass($clazz);
    if ($useParent) {
        $parentClass = $reflectionClass->getParentClass();
        if ($parentClass) {
            $reflectionClass = $parentClass;
        }
    }
    $reflectionProp = $reflectionClass->getProperty($prop);

    if ($reflectionProp->isPrivate() || $reflectionProp->isProtected()) {
        $reflectionProp->setAccessible(true);
    }

    if (is_null($value)) { /// doing a get?
        if (is_null($obj)) {
            $retValue = $reflectionProp->getValue();
        }
        else {
            $retValue = $reflectionProp->getValue($obj);
        }
    }
    else {
        if (is_null($obj)) {
            $retValue = $reflectionProp->getValue();
            $reflectionProp->setValue($value);
        }
        else {
            $retValue = $reflectionProp->getValue($obj);
            $reflectionProp->setValue($obj, $value);
        }
    }

    if ($reflectionProp->isPrivate() || $reflectionProp->isProtected()) {
        $reflectionProp->setAccessible(false);
    }

    return $retValue;
}
*/