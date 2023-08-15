<?php
namespace RainCity\Csv;

/**
 * Annotation for binding a name to a method or property in a class to aid in
 * loadind CSV files.
 *
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class CsvBindByName
{
    /**
     * Used to specific the column name in a CSV to be bound to the method or
     * property.
     *
     * @Required
     */
    public string $column;
    
    /**
     * Used to provide alternative column names to be bound to the method or
     * property.
     */
    public array $alternates = array();
}
