<?php declare(strict_types=1);
namespace RainCity\Json;

use JsonMapper\Middleware\Rename\Rename;

/**
 * Base class for classes representing JSON objects.
 */
abstract class JsonEntity
{
    /**
     * Mapping of JSON field names to class properties.
     *
     * Used to generate the field names to fetch and the Rename object for
     * mapping the array indexes to class properties.
     *
     * When an API returns JSON Lists instead of objects the fieldMap must
     * contain entries for all of the fields in the list in the appropriate
     * order. Additionally, the byIndex property must be set to 'true'.
     *
     * @return array An array of FieldPropertyEntry objects defining the mapping
     *      between JSON field and object property
     */
    protected abstract static function getFieldPropertyMap(): array;

    //static array $fieldPropertyMap = array();

    /**
     * Indicator as to whether mapping of fields to properties should be be
     * done by the index into the fieldMap or by the field name.
     *
     * Some REST APIs return lists and not objects in which case it is
     * necessary to have setup te fieldMap with all of the fields in the list
     * and then map them by index.
     *
     * @return bool True if the fields should be mapped by index, false if they
     *      should be mapped by name.
     */
    protected static function isMapByIndex(): bool
    {
        return false;
    }

    /**
     * Fetch the JSON field names defined in the fieldMap.
     *
     * @return string[]
     */
    public static function getJsonFields(): array
    {
        return array_map(fn($entry) => $entry->getField(), static::getFieldPropertyMap());
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

        $fieldMap = static::getFieldPropertyMap();

        array_walk(
            $fieldMap,
            fn($entry, $key) =>
                $renameObj->addMapping(
                    static::class,
                    static::isMapByIndex() ? strval($key) : $entry->getField(),
                    $entry->getProperty()
                    )
            );

        return $renameObj;
    }
}
