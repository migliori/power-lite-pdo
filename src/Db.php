<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo;

use PDO;
use Exception;
use Migliori\PowerLitePdo\Driver\DriverBase;
use Migliori\PowerLitePdo\Exception\DbException;
use Migliori\PowerLitePdo\Query\QueryBuilder;
use Migliori\PowerLitePdo\View\View;

/**
 * Db class - PDO Database abstraction layer class
 *
 * The Db class is a database abstraction layer that provides a simple, consistent interface
 * for interacting with different types of databases. It handles connection management, query execution,
 * pagination and result processing, allowing developers to focus on the business logic of their application.
 *
 * Full documentation with code examples is available here: {@link [https://www.powerlitepdo.com/] [https://www.powerlitepdo.com/]}
 *
 * The Db class is designed to be flexible and extensible, allowing developers to easily customize it
 * to meet their specific needs. It supports multiple database types, including MySQL, PostgreSQL, Firebird,
 * and Oracle, and can be easily extended to support additional databases.

 * The Db class is designed to be easy to use and understand. It provides a set of simple, intuitive methods
 * for executing queries and retrieving data, and it automatically handles error handling and debugging.
 * This makes it easy for developers to quickly get up and running with the class, without having to worry
 * about low-level details such as database connections and query execution.

 * In addition, the Db class is designed to be highly efficient and fast. It uses the latest database features
 * and optimization techniques to ensure that queries are executed quickly and efficiently, without sacrificing
 * performance. This means that applications built using the Db class can scale easily and perform well under
 * load, even with large amounts of data.
 *
 * @api
 * @author  Gilles Migliori
 * @version 1.0.0
 * @license GNU General Public License v3.0
 * @link    https://github.com/gilles-migliori/php-pdo-db-class
 * @link    https://packagist.org/packages/gilles-migliori/php-pdo-db-class
 * @link    https://www.phpformbuilder.pro/documentation/db-help.php
 */
class Db
{
    protected DriverBase $connection;

    protected PDO $pdo;

    protected QueryBuilder $queryBuilder;

    /**
     * Constructor
     */
    public function __construct(
        DriverBase $driverBase,
        QueryBuilder $queryBuilder
    ) {
        $this->connection   = $driverBase;
        $this->pdo          = $driverBase->getPdo();
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Returns the PDO object.
     *
     * @return PDO The PDO object.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Returns an instance of the QueryBuilder class.
     *
     * @return QueryBuilder An instance of the QueryBuilder class.
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * Executes a SQL query on the database.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string, mixed> $placeholders An indexed or associative array to store the placeholders used in the query.
     * @param bool|string $debug false, true or silent.
     * @return bool|int Returns true or false for a SELECT query, returns the number of affected rows for other statements or false if there was an error.
     */
    public function query(
        string $sql,
        array $placeholders = [],
        $debug = false
    ) {
        return $this->queryBuilder
            ->query($sql)
            ->placeholders($placeholders)
            ->debugOnce($debug)
            ->execute();
    }

    /**
     * Executes a SQL query using PDO and returns one row
     *
     * @param string $sql The SQL query to execute.
     * @param array<string, mixed> $placeholders An indexed or associative array to store the placeholders used in the query.
     * @param int $fetchParameters PDO fetch style record options
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed Array or object with values if success otherwise false
     */
    public function queryRow(
        string $sql,
        array $placeholders = [],
        int $fetchParameters = PDO::FETCH_OBJ,
        $debug = false
    ) {
        // It's better on resources to add LIMIT 1 to the end of your SQL
        // statement if there are multiple rows that will be returned
        $this->query($sql, $placeholders, $debug);

        return $this->fetch($fetchParameters);
    }

    /**
     * Executes a SQL query using PDO and returns a single value only
     *
     * @param string $sql The SQL query to execute.A
     * @param array<string, mixed> $placeholders An indexed or associative array to store the placeholders used in the query.
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed A returned value from the database if success otherwise false
     */
    public function queryValue(
        string $sql,
        array $placeholders = [],
        $debug = false
    ) {
        // It's better on resources to add LIMIT 1 to the end of your SQL
        // if there are multiple rows that will be returned
        $results = $this->queryRow($sql, $placeholders, PDO::FETCH_NUM, $debug);

        // If a record was returned
        if (is_array($results)) {
            // Return the first element of the array which is the first row
            return $results[0];
        }

        // No records were returned
        return false;
    }

    /**
     * Selects data from the database.
     *
     * @param string $from The SQL FROM statement with optional joins.
     *                     Example: 'table1 INNER JOIN table2 ON table1.id = table2.id'.
     * @param string|array<string> $fields The columns to select. Can be a string or an array of strings.
     * @param array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                     If it's an array, the conditions will be joined with AND.
     * @param array<string, bool|int|string> $parameters An associative array of parameter names and values.
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return bool|int True if the query was successful, false otherwise.
     */
    public function select(
        string $from,
        $fields,
        $where = [],
        array $parameters = [],
        $debug = false
    ) {
        return $this->queryBuilder
            ->select($fields)
            ->from($from)
            ->where($where)
            ->parameters($parameters)
            ->debugOnce($debug)
            ->execute();
    }

    /**
     * Select COUNT records using PDO
     *
     * @param string $from The SQL FROM statement with optional joins.
     *                     Example: 'table1 INNER JOIN table2 ON table1.id = table2.id'.
     * @param string|array<string> $fields The columns to select. Can be a string or an array of strings.
     * @param array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                     If it's an array, the conditions will be joined with AND.
     * @param array<string, bool|int|string> $parameters An associative array of parameter names and values.
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed The record row or false if no record has been found
     */
    public function selectCount(
        string $from,
        $fields = ['*' => 'rowsCount'],
        $where = [],
        array $parameters = [],
        $debug = false
    ) {
        $countFields = [];
        // If the fields are in an array
        if (is_array($fields)) {
            // Build the COUNT queries with aliases
            foreach ($fields as $key => $value) {
                $countFields[] = 'COUNT(' . $key . ') AS ' . $value;
            }
        } else { // It's a string
            // field AS f, field2 AS f2, DISTINCT fielf3 AS f3
            $str_fields = explode(', ', $fields);
            foreach ($str_fields as $str_field) {
                $countFields[] = 'COUNT(' . str_replace(' AS ', ') AS ', $str_field);
            }
        }

        $this->select(
            $from,
            $countFields,
            $where,
            $parameters,
            $debug
        );

        return $this->queryBuilder->fetch();
    }

    /**
     * Selects a single record using PDO
     *
     * @param string $from The SQL FROM statement with optional joins.
     *                     Example: 'table1 INNER JOIN table2 ON table1.id = table2.id'.
     * @param string|array<string> $fields The columns to select. Can be a string or an array of strings.
     * @param array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                     If it's an array, the conditions will be joined with AND.
     * @param int $fetchParameters PDO fetch style record options
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed Array or object with values if success otherwise false
     */
    public function selectRow(
        string $from,
        $fields = '*',
        $where = [],
        int $fetchParameters = PDO::FETCH_OBJ,
        $debug = false
    ) {
        $this->select($from, $fields, $where, ['limit' => 1], $debug);

        return $this->fetch($fetchParameters);
    }

    /**
     * Selects a single value using PDO
     *
     * @param string $from The SQL FROM statement with optional joins.
     *                     Example: 'table1 INNER JOIN table2 ON table1.id = table2.id'.
     * @param string|array<string> $field The columns to select. Can be a string or an array of strings.
     * @param array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                     If it's an array, the conditions will be joined with AND.
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed A returned value from the database if success otherwise false
     */
    public function selectValue(
        string $from,
        $field,
        $where = [],
        $debug = false
    ) {
        // Return the row
        $results = $this->selectRow($from, $field, $where, PDO::FETCH_NUM, $debug);

        // If a record was returned
        if (is_array($results)) {
            // Return the first element of the array which is the first row
            return $results[0];
        }

        // No records were returned
        return false;
    }

    /**
     * Inserts a new record into a table using PDO
     *
     * @param string $table Table name
     * @param array<string, mixed> $values Associative array containing the fields and values
     *                          e.g. ['name' => 'Cathy', 'city' => 'Cardiff']
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the insert is executed.
     *              true:     the insert is not executed. The query is displayed.
     *              'silent': the insert is not executed. The query is registered then can be displayed using the getDebug() method.
     * @return bool|int The number of affected rows or false if there was an error.
     */
    public function insert(
        string $table,
        array $values,
        $debug = false
    ) {
        if ($values === []) {
            throw new DbException('Failed to insert data into table "<em>' . $table . '</em>".<br>The array of values to be inserted cannot be empty.');
        }

        return $this->queryBuilder->insert($table, $values)->debugOnce($debug)->execute();
    }

    /**
     * Updates an existing record into a table using PDO
     *
     * @param string $table Table name
     * @param array<string, mixed> $values Associative array containing the fields and values
     *                          e.g. ['name' => 'Cathy', 'city' => 'Cardiff']
     * @param array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                     If it's an array, the conditions will be joined with AND.
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the update is executed.
     *              true     the update is not executed. The query is displayed.
     *              'silent': the update is not executed. The query is registered then can be displayed using the getDebug() method.
     * @return bool|int The number of affected rows or false if there was an error.
     */
    public function update(
        string $table,
        array $values,
        $where = [],
        $debug = false
    ) {
        if ($values === []) {
            throw new DbException('Failed to update data from table "<em>' . $table . '</em>".<br>The array of values to be updated cannot be empty.');
        }

        return $this->queryBuilder->update($table, $values, $where)->debugOnce($debug)->execute();
    }

    /**
     * Deletes a record from the database table.
     *
     * @param string $table The name of the table from which to delete the record.
     * @param array<int|string, mixed>|string $where An associative array of conditions to match the record(s) to be deleted.
     *                          The keys represent the column names and the values represent the matching values.
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the delete is executed.
     *              true     the delete is not executed. The query is displayed.
     *              'silent': the delete is not executed. The query is registered then can be displayed using the getDebug() method.
     * @return bool|int The number of affected rows or false if there was an error.
     */
    public function delete(
        string $table,
        $where = [],
        $debug = false
    ) {
        return $this->queryBuilder->delete($table, $where)->debugOnce($debug)->execute();
    }

    /**
     * Converts a Query() or Select() array of records into a simple array
     * using only one column or an associative array using another column as a key.
     *
     * @param mixed $array The array returned from a PDO query using fetchAll.
     * @param string $value_field The name of the field that holds the value.
     * @param string|null $key_field The name of the field that holds the key, making the return value an associative array.
     * @return array<mixed> Returns an array with only the specified data.
     */
    public function convertToSimpleArray($array, string $value_field, ?string $key_field = null): array
    {
        // Create an empty array
        $return = [];

        if (is_array($array)) {
            // Loop through the query results
            foreach ($array as $element) {
                // If we have a key
                if (!is_null($key_field)) {
                    // Add this key
                    $return[$element[$key_field]] = $element[$value_field];
                } else { // No key field
                    // Append to the array
                    $return[] = $element[$value_field];
                }
            }
        }

        // Return the new array
        return $return;
    }

    /**
     * Get the information about the columns in a given table
     *
     * @param string $table The name of the table
     * @param int $fetchParameters [OPTIONAL] The PDO fetch style record options
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed An associative array that contains the columns data or false if the table doesn't have any column.
     * [
     *     'Field' => string, The name of the column.
     *     'Type' => string, The column data type.
     *     'Null' => string, The column nullability. The value is YES if NULL values can be stored in the column, NO if not.
     *     'Key' => string, The column key if the column is indexed.
     *     'Default' => mixed, The default value for the column.
     *     'Extra' => string, Any additional information. The value is nonempty in these cases:
     *         - auto_increment for columns that have the AUTO_INCREMENT attribute.
     *         - on update CURRENT_TIMESTAMP for TIMESTAMP or DATETIME columns that have the ON UPDATE CURRENT_TIMESTAMP attribute.
     *         - VIRTUAL GENERATED or STORED GENERATED for generated columns.
     *         - DEFAULT_GENERATED for columns that have an expression default value.
     * ]
     */
    public function getColumns(string $table, int $fetchParameters = PDO::FETCH_OBJ, $debug = false)
    {
        $qry = $this->connection->getGetColumnsSql($table);

        $this->query($qry, [], $debug);

        return $this->fetchAll($fetchParameters);
    }

    /**
     * Returns the columns names of the target table in a table
     *
     * @param string $table The name of the table
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed An array that contains the columns names or false if the table doesn't have any column.
     */
    public function getColumnsNames(string $table, $debug = false)
    {
        $columns = $this->getColumns($table, PDO::FETCH_ASSOC, $debug);

        if (!$columns) {
            return false;
        }

        $fieldname = $this->connection->getDriverColumnName();

        return $this->convertToSimpleArray($columns, $fieldname);
    }

    /**
     * Selects all the tables into the database
     *
     * @param bool|string $debug false, true or 'silent'.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     * @return mixed Array with tables if success otherwise false
     */
    public function getTables($debug = false)
    {
        $qry = $this->connection->getGetTablesSql();

        $this->query($qry, [], $debug);

        return $this->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Begin transaction processing
     *
     * @return bool Returns true if the transaction begins successfully, false otherwise.
     * @throws Exception If there is an error.
     */
    public function transactionBegin(): bool
    {
        return $this->queryBuilder->transactionBegin();
    }

    /**
     * Commit and end transaction processing.
     *
     * @return bool Returns true if the transaction is committed successfully, false otherwise.
     *
     * @throws Exception If there is an error.
     */
    public function transactionCommit(): bool
    {
        return $this->queryBuilder->transactionCommit();
    }

    /**
     * Roll back transaction processing.
     *
     * @return bool Returns true if the transaction is rolled back successfully, false otherwise.
     *
     * @throws Exception If there is an error.
     */
    public function transactionRollback(): bool
    {
        return $this->queryBuilder->transactionRollback();
    }

    /**
     * Fetches the next row from a result set and returns it according to the $fetchParameters format
     *
     * @param int $fetchParameters The PDO fetch style record options
     * @return mixed The next row or false if we reached the end
     */
    public function fetch(int $fetchParameters = PDO::FETCH_OBJ)
    {
        return $this->queryBuilder->fetch($fetchParameters);
    }

    /**
     * Fetches all rows from a result set and return them according to the $fetchParameters format
     *
     * @param int $fetchParameters The PDO fetch style record options
     * @return mixed The rows according to PDO fetch style or false if no record
     */
    public function fetchAll(int $fetchParameters = PDO::FETCH_OBJ)
    {
        return $this->queryBuilder->fetchAll($fetchParameters);
    }

    /**
     * This function returns records from a SQL query as an HTML table.
     *
     * @param array<mixed> $records The records set - can be an array or array of objects according to the fetch parameters.
     * @param bool $showCount (Optional) true if you want to show the row count, false if you do not want to show the count.
     * @param string|null $tableAttr (Optional) Comma separated attributes for the table. e.g: 'class=my-class, style=color:#222'.
     * @param string|null $th_Attr (Optional) Comma separated attributes for the header row. e.g: 'class=my-class, style=font-weight:bold'.
     * @param string|null $tdAttr (Optional) Comma separated attributes for the cells. e.g: 'class=my-class, style=font-weight:normal'.
     * @return string HTML containing a table with all records listed.
     */
    public function getHTML(
        array $records,
        bool $showCount = true,
        ?string $tableAttr = null,
        ?string $th_Attr = null,
        ?string $tdAttr = null
    ): string {
        return $this->queryBuilder->getHTML($records, $showCount, $tableAttr, $th_Attr, $tdAttr);
    }

    /**
     * Get the last insert ID.
     *
     * @return bool|string The last insert ID or false if there was an error.
     */
    public function getLastInsertId()
    {
        return $this->queryBuilder->getLastInsertId();
    }

    /**
     * Retrieves the maximum value of a specified field from a given table.
     *
     * @param string $table The name of the table.
     * @param string $field The name of the field.
     * @return mixed The maximum value of the specified field or false if no value is found.
     */
    public function getMaximumValue(string $table, string $field)
    {
        return $this->queryBuilder->getMaximumValue($table, $field);
    }

    /**
     * Returns the number of rows in the result set of the current query.
     *
     * @return int|false The number of rows, or false on failure.
     */
    public function numRows()
    {
        return $this->queryBuilder->numRows();
    }

    /**
     * Sets the queryBuilder's debugOnceMode.
     *
     * @param bool|string $mode The debug mode to set.
     */
    public function debugOnce($mode): self
    {
        $this->queryBuilder->debugOnce($mode);
        return $this;
    }

    /**
     * Returns the debug information as a string.
     *
     * @return View The debug information.
     */
    public function getDebug(): View
    {
        return $this->queryBuilder->getDebug();
    }

    /**
     * Sets the queryBuilder's debugGlobalMode.
     *
     * @param bool|string $mode The debug mode to set. Pass `null` to disable debug mode.
     */
    public function setDebug($mode): void
    {
        $this->queryBuilder->setDebug($mode);
    }

    /**
     * Get the debug mode of the query builder.
     *
     * @return bool|string The debug mode of the query builder.
     */
    public function getDebugMode()
    {
        return $this->queryBuilder->getDebugMode();
    }
}
