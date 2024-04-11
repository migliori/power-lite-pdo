<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Query;

use PDO;

class Utilities
{
    /**
     * Retrieves the attributes for a given attribute name.
     *
     * @param string $attr The name of the attribute.
     * @return string The value of the attribute
     */
    public static function getAttributes($attr): string
    {
        if (empty($attr)) {
            return '';
        }

        $cleanAttr = '';
        // replace protected commas with expression
        $attr = str_replace('\\,', '[comma]', $attr);
        // replace protected equals with expression
        $attr = str_replace('\\=', '[equal]', $attr);
        // split with commas
        $attr = preg_split('`,`', $attr);
        if ($attr !== false) {
            foreach ($attr as $a) {
                // add quotes
                $a = preg_match('`=`', $a) ? preg_replace('`\s*=\s*`', '="', trim($a)) .  '" ' : trim($a) . ' ';
                $cleanAttr .= $a;
            }
        }

        // get back protected commas, equals and trim
        $cleanAttr = trim(str_replace(['[comma]', '[equal]'], [',', '='], $cleanAttr));
        return $cleanAttr;
    }

    /**
     * Returns the data type of the given value.
     *
     * @param mixed $value The value to determine the data type of.
     * @return int The data type of the value.
     */
    public static function getDataType($value): int
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        }

        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }

        if (is_null($value)) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }

    /**
     * Interpolates the given query string with the provided placeholders.
     *
     * @param string $queryString The query string to be interpolated.
     * @param array<int|string, mixed> $placeholders An associative array of placeholders and their corresponding values.
     * @return string The interpolated query string.
     */
    public static function interpolateQuery(string $queryString, array $placeholders): string
    {
        $keys = [];
        $values = [];

        if ($placeholders === []) {
            return $queryString;
        }

        // Sort $placeholders by key length in descending order.
        uksort($placeholders, static function ($a, $b): int {
            return strlen((string)$b) - strlen((string)$a);
        });

        // Build a regular expression for each parameter.
        foreach ($placeholders as $key => $value) {
            // Handle both named and indexed placeholders
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                // For indexed placeholders, we support both :1 and ? styles
                $keys[] = '/(:' . ($key + 1) . '|\\?)/';
            }

            if (is_string($value)) {
                $values[] = "'" . $value . "'";
            } elseif (is_array($value)) {
                $values[] = "'" . implode("','", $value) . "'";
            } elseif (is_null($value)) {
                $values[] = 'NULL';
            } else {
                $values[] = is_object($value) ? serialize($value) : var_export($value, true);
            }
        }

        $index = 0;
        $interpolatedQuery = preg_replace_callback($keys, function ($matches) use (&$index, $values) {
            return $values[$index++] ?? '';
        }, $queryString);

        return (string) $interpolatedQuery;
    }

    /**
     * test if the SQL query accepts transaction or if it's an auto-commited query
     * https://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html
     * @param string $sql
     */
    public static function isSqlAutoCommit($sql): bool
    {
        return preg_match('/ALTER (DATABASE|EVENT|PROCEDURE|SERVER|TABLE|TABLESPACE|VIEW)/i', $sql) || preg_match('/CREATE (DATABASE|EVENT|INDEX|PROCEDURE|SERVER|TABLE|TABLESPACE|TRIGGER|VIEW)/i', $sql) || preg_match('/(DROP (DATABASE|EVENT|INDEX|PROCEDURE|SERVER|TABLE|TABLESPACE|TRIGGER|VIEW)|INSTALL PLUGIN|LOCK TABLES|RENAME TABLE|TRUNCATE TABLE|UNINSTALL PLUGIN)/i', $sql);
    }
}
