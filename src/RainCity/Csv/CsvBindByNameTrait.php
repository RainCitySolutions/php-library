<?php
namespace RainCity\Csv;

use ReflectionProperty;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * A Trait work in conjuction with CsvBindByName annotation.
 *
 */
trait CsvBindByNameTrait
{
    /**
     * Generates an array of mappings between the value specified in the
     * CsvBindByName annotation and the property in the class.
     *
     * @return array An associative array of header names in the CSV to class
     *      property names.
     */
    public static function getColumnPropertyMap(): array
    {
        $result = array();

        $reader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_called_class());
        
        /** @var ReflectionProperty[] */
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            /** @var CsvBindByName */
            $anno = $reader->getPropertyAnnotation(
                $property,
                CsvBindByName::class
                );

            if (isset($anno)) {
                $result[$anno->column] =  $property->name;
            }
        }

        return $result;
    }

    /**
     * Fetch the list of CSV column names.
     *
     * @return array The array of column names.
     */
    public static function getColumnNames(): array
    {
        $result = array();

        $reader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_called_class());

        /** @var ReflectionProperty[] */
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            /** @var CsvBindByName */
            $anno = $reader->getPropertyAnnotation(
                $property,
                CsvBindByName::class
                );

            if (isset($anno)) {
                array_push($result, $anno->column);
            }
        }

        return $result;
    }

    /**
     * Fetch a map of CSV column name to property value.
     *
     * @return array An associative array of header names in the CSV to
     *      property values.
     */
    public static function getColumnValues($obj): array
    {
        assert(is_a($obj, get_called_class()));

        $result = array();

        $reader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_called_class());

        /** @var ReflectionProperty[] */
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            /** @var CsvBindByName */
            $anno = $reader->getPropertyAnnotation(
                $property,
                CsvBindByName::class
                );
           if (isset($anno)) {
               $result[$anno->column] = $property->isInitialized($obj) ? $property->getValue($obj) : '';
            }
        }

        return $result;
    }
}
