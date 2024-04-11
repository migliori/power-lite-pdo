<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Query;

use DateTime;
/**
 * Represents a WHERE clause in a SQL query.
 *
 * This class provides methods for building a WHERE clause in a SQL query.
 */
class Where
{
    /**
     * @var string SQL query string
     */
    private string $sql = '';

    /**
     * @var array<string, mixed> Placeholder values for PDO prepared statements
     */
    private array $placeholders = [];

    /**
     * Constructor.
     *
     * Builds a SQL WHERE clause from an array
     *
    * @param array<int|string, mixed>|string $where String or Array containing the fields and values or a string
     *                    Example:
     *                    $where = 'id > 1234';
     *                    or:
     *                    $where['id >'] = 1234;
     *                    $where[] = 'first_name IS NOT NULL';
     *                    $where['some_value <>'] = 'text';
     */
    public function set($where = ''): void
    {
        // If an array was passed in...
        if (is_array($where) && $where !== []) {
            // Create an array to hold the WHERE values
            $output = [];

            // remove any empty values
            $where = array_filter($where);

            // loop through the array
            foreach ($where as $key => $value) {
                // If the value is a DateTime object...
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                // If a key is specified for a PDO place holder field...
                if (is_string($key)) {
                    // Extract the key
                    $extracted_key = (string) preg_replace(
                        '/^(\s*)([^\s=<>]*)(.*)/',
                        '${2}',
                        $key
                    );

                    $extracted_key = str_replace('.', '_', $extracted_key);

                    // avoid duplicate keys
                    // and use a prefix to avoid collisions with the $values in select/update queries
                    // use a letter index before the $extracted_key
                    // because Firebird bugs if we use $extracted_key . '_' . $index
                    $index = 0;
                    $alphabet = range('a', 'z');
                    $indexed_key = $alphabet[$index] . '_' . $extracted_key;
                    while (isset($this->placeholders[$indexed_key])) {
                        ++$index;
                        $indexed_key = $alphabet[$index] . '_' . $extracted_key;
                    }

                    $extracted_key = (string) $indexed_key;

                    // If no <> = was specified...
                    if ($alphabet[$index] . '_' . trim(str_replace('.', '_', $key)) === $extracted_key) {
                        // Add the PDO place holder with an =
                        $output[] = trim($key) . ' = :' . $extracted_key;
                    } else { // A comparison exists...
                        // Add the PDO place holder
                        $output[] = trim($key) . ' :' . $extracted_key;
                    }

                    // Add the placeholder replacement values
                    $this->placeholders[$extracted_key] = $value;
                } else { // No key was specified...
                    $output[] = $value;
                }
            }

            // Concatenate the array values
            $this->sql = ' WHERE ' . implode(' AND ', $output);
        } elseif (is_string($where) && ($where !== '' && $where !== '0')) {
            $this->sql = ' WHERE ' . trim($where);
        } else {
            $this->sql = '';
            $this->placeholders = [];
        }
    }

    /**
     * Resets the conditions in the WHERE clause of the query.
     *
     * This method clears any previously set conditions in the WHERE clause of the query,
     * allowing you to start building a new set of conditions.
     */
    public function reset(): void
    {
        $this->sql = '';
        $this->placeholders = [];
    }

    /**
     * Get the SQL string for the WHERE clause.
     *
     * @return string The SQL string for the WHERE clause.
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Get the placeholders used in the query.
     *
     * @return array<string, mixed> The array of placeholders.
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }
}
