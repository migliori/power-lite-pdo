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
     * The driver name ('mysql', 'pgsql', 'firebird', 'oci'), used to translate
     * the case-insensitive ILIKE operator. Empty when the Where instance is
     * used standalone (ILIKE then falls back to LIKE).
     */
    private string $driver = '';

    /**
     * Sets the driver name used to translate the ILIKE operator.
     *
     * Called by QueryBuilder, which always knows the connected driver.
     *
     * @param string $driver The driver name ('mysql', 'pgsql', 'firebird', 'oci').
     */
    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Builds the SQL WHERE clause from an array or a raw string.
     *
    * @param array<int|string, mixed>|string $where String or Array containing the fields and values or a string
     *                    Example:
     *                    $where = 'id > 1234';
     *                    or:
     *                    $where['id >'] = 1234;
     *                    $where[] = 'first_name IS NOT NULL';
     *                    $where['some_value <>'] = 'text';
     *                    $where['first_name ILIKE'] = '%pen%'; // case-insensitive LIKE, translated per driver
     */
    public function set($where = ''): void
    {
        // If an array was passed in...
        if (is_array($where) && $where !== []) {
            // Create an array to hold the WHERE values
            $output = [];

            // remove any empty values
            // preserve 0 / '0' / 0.0 (falsy values are valid WHERE conditions),
            // drop '' and null (empty filters must not constrain queries)
            $where = array_filter($where, static function ($v) {
                return $v !== '' && $v !== null;
            });

            // loop through the array
            foreach ($where as $key => $value) {
                // If the value is a DateTime object...
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                // If a key is specified for a PDO place holder field...
                if (is_string($key)) {
                    // Case-insensitive LIKE: detect the ILIKE operator in the key
                    // ('field ILIKE' or 'field NOT ILIKE') and translate it per driver:
                    // - pgsql          : native ILIKE / NOT ILIKE
                    // - mysql          : LIKE / NOT LIKE (default collations are case-insensitive)
                    // - firebird, oci  : UPPER(field) LIKE / NOT LIKE (value is uppercased below)
                    $ilike_field = '';
                    $ilike_not   = false;
                    if (preg_match('/^(.*\S)\s+NOT\s+ILIKE$/i', $key, $matches)) {
                        $ilike_field = $matches[1];
                        $ilike_not   = true;
                    } elseif (preg_match('/^(.*\S)\s+ILIKE$/i', $key, $matches)) {
                        $ilike_field = $matches[1];
                    }
                    if ($ilike_field !== '' && ($this->driver === 'firebird' || $this->driver === 'oci') && is_string($value)) {
                        $value = strtoupper($value);
                    }

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
                    } elseif ($ilike_field !== '') {
                        // Add the PDO place holder with the driver-translated ILIKE condition
                        $output[] = $this->getIlikeCondition($ilike_field, $ilike_not, $extracted_key);
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
     * Builds the driver-specific condition for the ILIKE (case-insensitive LIKE) operator.
     *
     * @param string $field       The field name or expression.
     * @param bool   $not         True to negate the condition (NOT ILIKE).
     * @param string $placeholder The PDO placeholder name, without the leading colon.
     * @return string The SQL condition.
     */
    private function getIlikeCondition(string $field, bool $not, string $placeholder): string
    {
        $not_sql = $not ? 'NOT ' : '';
        switch ($this->driver) {
            case 'pgsql':
                // native case-insensitive operator
                return $field . ' ' . $not_sql . 'ILIKE :' . $placeholder;
            case 'firebird':
            case 'oci':
                // no native ILIKE: compare the uppercased field with the
                // uppercased value (uppercased in set())
                return 'UPPER(' . $field . ') ' . $not_sql . 'LIKE :' . $placeholder;
            case 'mysql':
            default:
                // MySQL / MariaDB default *_ci collations already make
                // LIKE case-insensitive; without a known driver, plain LIKE
                // is the safest portable fallback
                return $field . ' ' . $not_sql . 'LIKE :' . $placeholder;
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
