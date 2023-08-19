<?php
namespace RainCity\Csv;

/**
 * Annotation for binding a name or names to a method or property in a class
 * to aid in loading CSV files (through LeagueCsv though may be useable for
 * other packages).
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD","PROPERTY"})
 */
final class CsvBindByName
{
    private const INVALID_ARG_MSG = '\'column\' property can only be a string, or an array of strings';

    /**
     * Used to specific the column name(s) in a CSV to be bound to the method or
     * property.
     *
     * @Required
     */
    private array $columns;
    
    public function __construct($column)
    {
        if (empty($column)) {
            throw new \InvalidArgumentException(self::INVALID_ARG_MSG);
        }
        
        if (is_string($column)) {
            $column = array($column);
        }
        
        if (is_array($column)) {
            if (!empty(array_filter($column, fn($entry) => !is_string($entry) || empty(trim($entry))))) {
                throw new \InvalidArgumentException(self::INVALID_ARG_MSG);
            }
            $this->columns = array_map(fn($entry) => trim($entry), $column);
        } else {
            throw new \InvalidArgumentException(self::INVALID_ARG_MSG);
        }
    }
    
    public function getColumns(): array
    {
        return $this->columns;
    }
}
