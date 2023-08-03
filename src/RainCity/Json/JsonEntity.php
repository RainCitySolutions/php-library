<?php
namespace RainCity\Json;

use JsonMapper\Middleware\Rename\Rename;

/**
 * Base class for classes representing JSON objects.
 */
abstract class JsonEntity
{
    /**
     * Mapping of RaceResult field names to class properties.
     *
     * Used to generate the field names to fetch and the Rename object for
     * mapping the array indexes to class properties.
     *
     * @var FieldPropertyEntry[]
     */
    static array $fieldMap = array();
    
    /**
     * Fetch the RaceResult field names defined in the fieldMap.
     *
     * @return string[]
     */
    public static function getFields(): array
    {
        return array_map(fn($entry) => $entry->getField(), self::$fieldMap);
    }
    
    /**
     * Fetch the Rename map of array index to class properties.
     *
     * @return Rename
     */
    public static function getRenameMapping(): Rename
    {
        /** @var Rename */
        $renameObj = new Rename();
        
        array_walk(
            self::$fieldMap,
            fn($entry, $key) => $renameObj->addMapping(static::class, strval($key), $entry->getProperty())
            );
        
        return $renameObj;
    }
}
