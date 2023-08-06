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
     * @var FieldPropertyEntry[]
     */
    static array $fieldMap = array();

    /**
     * Indicator as to whether mapping of fields to properties should be be
     * done by the index into the fieldMap or by the field name.
     *
     * Some REST APIs return lists and not objects in which case it is
     * necessary to have setup te fieldMap with all of the fields in the list
     * and then map them by index.
     *
     * @var boolean
     */
    static bool $byIndex = false;

    /**
     * Fetch the JSON field names defined in the fieldMap.
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
            fn($entry, $key) =>
                $renameObj->addMapping(
                    static::class,
                    self::$byIndex ? strval($key) : $entry->getField(),
                    $entry->getProperty()
                    )
            );

        return $renameObj;
    }
}
