<?php
namespace RainCity\Csv;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionProperty;

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

        static::processAnnotations(
            $result,
            function (array &$result, CsvBindByName $anno, ReflectionProperty $property) {
                $result[$anno->column] =  $property->name;
                
                foreach ($anno->alternates as $alt) {
                    $result[$alt] = $property->name;
                }
            }
            );

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

        static::processAnnotations(
            $result,
            function (array &$result, CsvBindByName $anno, ReflectionProperty $property) {
                array_push($result, $anno->column);
                
                foreach ($anno->alternates as $alt) {
                    array_push($result, $alt);
                }
            }
            );

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

        static::processAnnotations(
            $result,
            function (array &$result, CsvBindByName $anno, ReflectionProperty $property) use ($obj) {
                $propValue = $property->isInitialized($obj) ? $property->getValue($obj) : '';
                $result[$anno->column] = $propValue;
                
                foreach ($anno->alternates as $alt) {
                    $result[$alt] = $propValue;
                }
            }
            );

        return $result;
    }
    
    private static function processAnnotations(array &$result, callable $callback): void
    {
        $reader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_called_class());
        
        /** @var ReflectionProperty[] */
        $properties = $reflectionClass->getProperties();
        
        foreach ($properties as $property) {
            $annotations = $reader->getPropertyAnnotations($property);
            
            foreach ($annotations as $anno) {
                if ($anno instanceof CsvBindByName) {
                    $callback($result, $anno, $property);
                }
            }
        }
    }
}
