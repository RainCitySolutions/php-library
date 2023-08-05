<?php declare(strict_types=1);
namespace RainCity\Json;

/**
 * The FieldPropertyEntry class represents the mapping between a JSON field
 * and a class property.
 */
class FieldPropertyEntry
{
    private $field;
    private $property;

    /**
     * Construct an instance of FieldPropertyEntry
     *
     * @param string $field A JSON field name
     * @param string $property A class property name
     */
    public function __construct(string $field, string $property)
    {
        $this->field = $field;
        $this->property = $property;
    }

    /**
     * Fetch the field name
     *
     * @return string The name of the field
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Fetch the property name
     *
     * @return string The name of the property
     */
    public function getProperty(): string
    {
        return $this->property;
    }
}
