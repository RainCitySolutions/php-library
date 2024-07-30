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
     * @var array<string>
     */
    private array $columns;

    /**
     *
     * @param string|array<string> $column
     */
    public function __construct(string|array $column)
    {
        if (empty($column)) {
            throw new \InvalidArgumentException(self::INVALID_ARG_MSG);
        }

        if (is_string($column)) {
            $column = array($column);
        }

        if (!empty(array_filter($column, fn($entry) => !is_string($entry) || empty(trim($entry))))) {
            throw new \InvalidArgumentException(self::INVALID_ARG_MSG);
        }
        $this->columns = array_map(fn($entry) => trim($entry), $column);
    }

    /**
     *
     * @return array<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
