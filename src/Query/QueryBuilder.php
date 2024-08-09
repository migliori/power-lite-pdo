<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Query;

use DateTime;
use PDOException;
use Exception;
use PDOStatement;
use PDO;
use InvalidArgumentException;
use Migliori\PowerLitePdo\Driver\DriverBase;
use Migliori\PowerLitePdo\Exception\QueryBuilderException;
use Migliori\PowerLitePdo\Query\Parameters;
use Migliori\PowerLitePdo\Query\Where;
use Migliori\PowerLitePdo\Query\QueryType;
use Migliori\PowerLitePdo\Query\Utilities;
use Migliori\PowerLitePdo\Query\Debugger;
use Migliori\PowerLitePdo\Result\Result;
use Migliori\PowerLitePdo\View\View;

class QueryBuilder
{
    // Define the QueryTypes

    private DriverBase $connection;

    private string $from;

    private string $fields;

    /**
     * @var array<int|string, mixed> $placeholders An indexed or associative array to store the placeholders used in the query.
     */
    private array $placeholders = [];

    private string $queryString;

    private string $queryType;

    private string $rawQuery;

    private Where $where;

    private Parameters $parameters;

    private Result $result;

    /**
     * @var bool|string $lastInsertId
     */
    private $lastInsertId;

    private string $table;

    /**
     * @var array<string, mixed> $values Associative array containing the fields and values
     */
    private array $values;

    private Debugger $debugger;

    /**
     * Debug mode for the query builder.
     * debugGlobalMode is the global debug mode for the class.
     * debugOnceMode is the debug mode for a single query.
     *
     * If debugOnceMode is false, the global debug mode will be used.
     * If debugOnceMode is set, it will override the global debug mode.
     * Both can be set to false, true, or 'silent'.
     *
     * If set to true, the class will display detailed error messages.
     * If set to 'silent', the class will register all error messages, which can be retrieved using the getDebug() method.
     *
     * @var bool|string $debugOnceMode
     */
    private $debugOnceMode = false;

    /**
     * @var bool|string $debugGlobalMode
     */
    private $debugGlobalMode = false;

    /**
     * Constructor for the QueryBuilder class.
     *
     * @param DriverBase $driverBase The database connection object.
     */
    public function __construct(DriverBase $driverBase, Where $where, Parameters $parameters, Result $result)
    {
        $this->connection = $driverBase;
        $this->where      = $where;
        $this->parameters = $parameters;
        $this->result     = $result;
    }

    public function query(string $sql): self
    {
        $this->clear();
        $this->queryType = (new QueryType(QueryType::RAW))->getValue();
        $this->rawQuery  = $sql;
        return $this;
    }

    /**
     * Set the columns to select from the table.
     *
     * @param  string|array<string> $fields The columns to select. Can be a string or an array of strings.
     *                                      If it's an array, the columns will be joined with a comma.
     * @return self The current instance of the QueryBuilder.
     */
    public function select($fields): self
    {
        // Reset the query builder
        $this->clear();

        // Set the query type
        $this->queryType = (new QueryType(QueryType::SELECT))->getValue();

        // If the fields are in an array
        $this->fields = is_array($fields) ? implode(', ', array_map('trim', $fields)) : trim($fields);
        return $this;
    }

    /**
     * Set the table with optional JOIN clauses to select from.
     *
     * @param  string $from The SQL FROM statement with optional joins.
     *                      Example: 'table1 INNER JOIN table2 ON table1.id = table2.id'.
     * @return $this The current instance of the QueryBuilder.
     */
    public function from(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Set the WHERE clause for the query.
     *
     * @param  array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                                                If it's an array, the conditions will be joined with AND.
     * @return self The current instance of the QueryBuilder.
     */
    public function where($where): self
    {
        $this->where->set($where);
        $this->placeholders = $this->where->getPlaceholders();
        return $this;
    }

    /**
     * Sets the DISTINCT keyword in the query.
     */
    public function distinct(): self
    {
        $this->parameters->set('selectDistinct', true);
        return $this;
    }

    /**
     * Set the GROUP BY clause for the query.
     *
     * @param  string $groupBy The GROUP BY clause.
     * @return self The current instance of the QueryBuilder.
     */
    public function groupBy(string $groupBy): self
    {
        $this->parameters->set('groupBy', $groupBy);
        return $this;
    }

    /**
     * Set the LIMIT clause for the query.
     *
     * @param  int|string $limit The LIMIT clause.
     * @return self The current instance of the QueryBuilder.
     */
    public function limit($limit): self
    {
        $this->parameters->set('limit', $limit);
        return $this;
    }

    /**
     * Set the ORDER BY clause for the query.
     *
     * @param  string $orderBy The ORDER BY clause.
     * @return self The current instance of the QueryBuilder.
     */
    public function orderBy(string $orderBy): self
    {
        $this->parameters->set('orderBy', $orderBy);
        return $this;
    }

    /**
     * Set the parameters for the query.
     *
     * @param  array<string, bool|int|string> $parameters An associative array of parameter names and values.
     * @return self The QueryBuilder instance.
     */
    public function parameters(array $parameters): self
    {
        foreach ($parameters as $key => $value) {
            $this->parameters->set($key, $value);
        }

        return $this;
    }

    /**
     * Set the placeholders for the query.
     *
     * @param  array<int|string, mixed> $placeholders An indexed or associative array to store the placeholders used in the query.
     * @return self The QueryBuilder instance.
     */
    public function placeholders(array $placeholders): self
    {
        foreach ($placeholders as $key => $value) {
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
                $placeholders[$key] = $value;
            }
        }

        $this->placeholders = $placeholders;
        return $this;
    }

    /**
     * Insert a new row into the specified table with the given values.
     *
     * @param  string               $table  The name of the table to insert into.
     * @param  array<string, mixed> $values An associative array of column-value pairs to insert.
     * @return self The QueryBuilder instance for method chaining.
     */
    public function insert(string $table, array $values): self
    {
        // Reset the query builder
        $this->clear();

        $this->queryType = (new QueryType(QueryType::INSERT))->getValue();
        $this->table = $table;
        $this->values = $values;
        $this->placeholders = $values;
        return $this;
    }

    /**
     * Update records in the specified table.
     *
     * @param  string                          $table  The name of the table to update.
     * @param  array<string, mixed>            $values An associative array of column-value pairs to update.
     * @param  array<int|string, mixed>|string $where  The condition to filter the records to be updated. Can be a string or an array of conditions.
     *                                                 If it's an array, the conditions will be joined with AND.
     * @return self The current instance of the QueryBuilder.
     */
    public function update(string $table, array $values, $where): self
    {
        // Reset the query builder
        $this->clear();

        $this->queryType = (new QueryType(QueryType::UPDATE))->getValue();
        $this->table = $table;
        $this->where->set($where);
        $this->values = $values;
        $this->placeholders = array_merge($values, $this->where->getPlaceholders());
        return $this;
    }

    /**
     * Deletes records from the specified table based on the given conditions.
     *
     * @param  string                          $table The name of the table to delete records from.
     * @param  array<int|string, mixed>|string $where The condition(s) to be used in the delete query.
     * @return self The current instance of the QueryBuilder.
     */
    public function delete(string $table, $where): self
    {
        // Reset the query builder
        $this->clear();

        $this->queryType = (new QueryType(QueryType::DELETE))->getValue();
        $this->table = $table;
        $this->where->set($where);
        $this->placeholders = $this->where->getPlaceholders();
        return $this;
    }

    /**
     * Executes a raw query and returns the result.
     *
     * @return bool|int The result of the query execution.
     */
    public function execute()
    {
        if ($this->queryType !== 'RAW') {
            if ($this->queryType === 'SELECT') {
                return $this->executeQuery();
            }

            return $this->executeStatement();
        }

        // check if $this->sql begins with 'SELECT'
        if (preg_match('/^\s*SELECT/i', $this->rawQuery)) {
            return $this->executeQuery();
        }

        return $this->executeStatement();
    }

    /**
     * Execute the SELECT query.
     *
     * @return bool True if the query was successful, false otherwise.
     */
    public function executeQuery(): bool
    {
        $query = null; // Initialize $query to null
        $sql = '';

        try {
            // Build the SQL query
            $sql = $this->queryType === 'RAW' ? $this->rawQuery : $this->getSqlForSelect();

            $pdo = $this->connection->getPdo();

            // Prepare the query
            $query = $pdo->prepare($sql);

            $this->queryString = $query->queryString;

            // If there are placeholders...
            $query = $this->bindValues($query);

            // Start a timer
            $timeStart = microtime(true);

            // Execute the query
            $query->execute();

            // Find out how long the query took
            $timeEnd = microtime(true);
            $time = $timeEnd - $timeStart;

            // Build the result set
            $this->result->set($query);

            // Output debug information
            $this->dumpDebug($query, $time);

            // Query was successful
            return true;
        } catch (PDOException $e) { // If there was a database error...
            // interpolate the query
            $interpolatedSql = Utilities::interpolateQuery($sql, $this->placeholders);
            // Get the error
            $errorMessage = 'Database Error (' . __METHOD__ . '):<br>' . $e->getMessage() . '<br>' . $interpolatedSql;

            // Output debug information
            $this->dumpDebug($query, null, $errorMessage);

            throw new QueryBuilderException($errorMessage);
        } catch (Exception $e) { // If there was a general error...
            // Get the error
            $errorMessage = 'General Error (' . __METHOD__ . '): ' . $e->getMessage();

            $query = (isset($query) && $query instanceof PDOStatement) ? $query : null;

            // Output debug information
            $this->dumpDebug($query, null, $errorMessage);

            throw new QueryBuilderException($errorMessage);
        }
    }

    /**
     * Executes the prepared statement and returns the result.
     *
     * @return bool|int The number of affected rows or false if there was an error.
     */
    public function executeStatement()
    {
        // Build the SQL query
        $sql = $this->queryType === 'RAW' ? $this->rawQuery : $this->getSqlStatement();

        $pdo = $this->connection->getPdo();

        // Initialize the query variable
        $query = null;

        // Set the variable initial values
        $time  = false;

        // reset the global PDOStatement::rowCount
        $affectedRows = 0;

        // Is there already a transaction pending? No nested transactions in MySQL!
        $inTransaction = $pdo->inTransaction();

        $isSqlAutoCommit = Utilities::isSqlAutoCommit($sql);

        try {
            // Prepare the query
            $query = $pdo->prepare($sql);

            // Begin a transaction
            if (!$inTransaction && !$isSqlAutoCommit) {
                $pdo->beginTransaction();
            }

            // If there are placeholders...
            $query = $this->bindValues($query);

            // Start a timer
            $timeStart = microtime(true);

            // Execute the query
            $query->execute();

            // Find out how long the query took
            $timeEnd = microtime(true);
            $time = $timeEnd - $timeStart;

            // Build the result set
            $this->result->set($query);

            $affectedRows = $query->rowCount();

            // register the lastInsertId if the driver supports it and the query is an INSERT
            if ($this->connection->driverSupportsLastInsertId() && ($this->queryType === 'INSERT' || ($this->queryType === 'RAW' && strpos($this->rawQuery, 'INSERT') !== false))) {
                $this->lastInsertId = $pdo->lastInsertId();
            }

            // Debug only
            if ($this->debugGlobalMode !== false || $this->debugOnceMode !== false) {
                // Rollback the transaction
                if (!$inTransaction && !$isSqlAutoCommit) {
                    $pdo->rollback();
                }

                // Output debug information
                $this->dumpDebug($query, $time);
            } elseif (!$inTransaction && !$isSqlAutoCommit) {
                // Commit the transaction
                $pdo->commit();
            }

            if ($affectedRows < 1) {
                return false;
            }

            return $affectedRows;
        } catch (PDOException $e) { // If there was an error...
            // Rollback the transaction
            if (!$inTransaction && !$isSqlAutoCommit && $pdo instanceof PDO) {
                $pdo->rollback();
            }

            // interpolate the query
            $interpolatedSql = Utilities::interpolateQuery($sql, $this->placeholders);

            // Get the error
            $errorMessage = 'Database Error (' . __METHOD__ . '):<br>' . $e->getMessage() . '<br>' . $interpolatedSql;

            // Output debug information
            $this->dumpDebug($query, null, $errorMessage);

            throw new QueryBuilderException($errorMessage);
        } catch (Exception $e) { // If there was an error...
            // Rollback the transaction
            if (!$inTransaction && !$isSqlAutoCommit && $pdo instanceof PDO) {
                $pdo->rollback();
            }

            // Get the error
            $errorMessage = 'General Error (' . __METHOD__ . '): ' . $e->getMessage();

            $query = (isset($query) && $query instanceof PDOStatement) ? $query : null;

            // Output debug information
            $this->dumpDebug($query, null, $errorMessage);

            throw new QueryBuilderException($errorMessage);
        }
    }

    /**
     * Begin transaction processing
     *
     * @return bool Returns true if the transaction begins successfully, false otherwise.
     * @throws Exception If there is an error.
     */
    public function transactionBegin(): bool
    {
        try {
            if ($this->connection->getDriver() === 'firebird') {
                $this->connection->getPdo()->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            }

            // Begin transaction processing
            $success = $this->connection->getPdo()->beginTransaction();
        } catch (Exception $exception) { // If there was an error...
            // Return false to show there was an error
            $success = false;

            throw new QueryBuilderException($exception->getMessage());
        }

        return $success;
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
        try {
            // Commit and end transaction processing
            $success = $this->connection->getPdo()->commit();
            if ($this->connection->getDriver() === 'firebird') {
                $this->connection->getPdo()->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
            }
        } catch (Exception $exception) {
            // If there was an error with the database connection
            $success = false;

            throw new QueryBuilderException($exception->getMessage());
        }

        return $success;
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
        try {
            // Roll back transaction processing
            $success = $this->connection->getPdo()->rollback();
            if ($this->connection->getDriver() === 'firebird') {
                $this->connection->getPdo()->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
            }
        } catch (Exception $exception) {
            // If there was an error with the database connection
            $success = false;

            throw new QueryBuilderException($exception->getMessage());
        }

        return $success;
    }

    /**
     * Fetches the next row from the result set as an object or an array.
     *
     * @param  int $fetch_parameters The fetch style to use. Default is \PDO::FETCH_OBJ.
     * @return mixed The fetched row as an object or an array, depending on the fetch style.
     */
    public function fetch(int $fetch_parameters = PDO::FETCH_OBJ)
    {
        return $this->result->fetch($fetch_parameters);
    }

    /**
     * Fetches all rows from the database using the specified fetch mode.
     *
     * @param  int $fetch_parameters The fetch mode to use. Defaults to \PDO::FETCH_OBJ.
     * @return mixed An array of rows fetched from the database.
     */
    public function fetchAll(int $fetch_parameters = PDO::FETCH_OBJ)
    {
        return $this->result->fetchAll($fetch_parameters);
    }

    /**
     * This function returns records from a SQL query as an HTML table.
     *
     * @param  array<mixed> $records   The records set - can be an array or array of objects according to the fetch parameters.
     * @param  bool         $showCount (Optional) true if you want to show the row count, false if you do not want to show the count.
     * @param  string|null  $tableAttr (Optional) Comma separated attributes for the table. e.g: 'class=my-class, style=color:#222'.
     * @param  string|null  $thAttr    (Optional) Comma separated attributes for the header row. e.g: 'class=my-class, style=font-weight:bold'.
     * @param  string|null  $tdAttr    (Optional) Comma separated attributes for the cells. e.g: 'class=my-class, style=font-weight:normal'.
     * @return string HTML containing a table with all records listed.
     */
    public function getHTML(
        array $records,
        bool $showCount = true,
        ?string $tableAttr = null,
        ?string $thAttr = null,
        ?string $tdAttr = null
    ): string {
        // Set default style information
        $tb = $tableAttr === null ? 'style="border-collapse:collapse;empty-cells:show"' : Utilities::getAttributes($tableAttr);
        if ($thAttr === null) {
            $th = 'style="border-width:1px;border-style:solid;background-color:navy;color:white"';
        } else {
            $th = Utilities::getAttributes($thAttr);
        }

        $td = $tdAttr === null ? 'style="border-width:1px;border-style:solid"' : Utilities::getAttributes($tdAttr);

        $view = new View();

        // If there was no error && records were returned...
        if (is_array($records) && $records !== []) {
            // Begin the table
            if ($showCount) {
                $view->add("<p>Total Count: " . count($records) . "</p>\n");
            }

            $view->add("<table {$tb}>\n");

            // Create the header row
            $view->add("\t<tr>\n");
            foreach ($records[0] as $key => $value) {
                $view->add(sprintf('		<th %s>', $th) . htmlspecialchars($key) . "</th>\n");
            }

            $view->add("\t</tr>\n");

            // Create the rows with data
            foreach ($records as $record) {
                $view->add("\t<tr>\n");
                foreach ($record as $value) {
                    if (is_null($value)) {
                        $value = '';
                    }

                    $view->add(sprintf('		<td %s>', $td) . htmlspecialchars((string) $value) . "</td>\n");
                }

                $view->add("\t</tr>\n");
            }

            // Close the table
            $view->add("</table>");
        } else { // No records were returned
            $view->add("No records were returned.");
        }

        // Return the table HTML code
        return $view->get();
    }

    /**
     * Get the last insert ID.
     *
     * @return bool|string The last insert ID or false if there was an error.
     */
    public function getLastInsertId()
    {
        if ($this->queryType !== 'INSERT' && $this->queryType !== 'RAW' && strpos($this->rawQuery, 'INSERT') === false) {
            throw new QueryBuilderException('getLastInsertId() can only be called after an INSERT query');
        }

        if ($this->connection->driverSupportsLastInsertId()) {
            return $this->lastInsertId;
        }

        throw new QueryBuilderException('The driver does not support lastInsertId(). Use getMaximumValue($table, $field) instead.');
    }

    /**
     * Get the maximum value from a specific table field.
     *
     * @return mixed the field value or false if no value is found.
     */
    public function getMaximumValue(string $table, string $field)
    {
        $this->select($field)
            ->from($table)
            ->orderBy($field . ' DESC')
            ->limit(1)
            ->executeQuery();

        $row = $this->fetch();

        if ($row) {
            return $row->$field;
        }

        return false;
    }

    /**
     * Get the fields to be selected in the query.
     * (for PHPUnit testing purposes only).
     *
     * @return string The fields to be selected.
     */
    public function getFields(): string
    {
        return $this->fields;
    }

    /**
     * Get the table name from the query builder.
     * (for PHPUnit testing purposes only).
     *
     * @return string The From query.
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * Get the parameters used in the query.
     * (for PHPUnit testing purposes only).
     *
     * @return Parameters The parameters used in the query.
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    /**
     * Get the query type.
     * (for PHPUnit testing purposes only).
     *
     * @return string The query type.
     */
    public function getQueryType(): string
    {
        return $this->queryType;
    }

    /**
     * Get the raw SQL query string.
     * (for PHPUnit testing purposes only).
     *
     * @return string The raw SQL query string.
     */
    public function getRawQuery(): string
    {
        return $this->rawQuery;
    }

    /**
     * Get the result of the query.
     * (for PHPUnit testing purposes only).
     *
     * @return Result The result of the query.
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * Get the table name associated with the query builder.
     * (for PHPUnit testing purposes only).
     *
     * @return string The table name.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the values of the query.
     * (for PHPUnit testing purposes only).
     *
     * @return array<string, mixed> The values of the query.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Get the Where object associated with this QueryBuilder.
     * (for PHPUnit testing purposes only).
     *
     * @return Where The Where object.
     */
    public function getWhere(): Where
    {
        return $this->where;
    }

    /**
     * Returns the number of rows in the result set of the current query.
     *
     * @return int|false The number of rows, or false on failure.
     */
    public function numRows()
    {
        $return = false;
        $time   = false;

        $pdo = $this->connection->getPdo();

        // Initialize the query variable
        $sql = '';

        $numRowsQuery = null;

        try {
            // default: will send the query and fetch all records to count them
            $useSelectCount = false;
            $sql = $this->queryString;

            // If the query string has no "limit" terms
            // and can be parsed as a SELECT FROM query
            // OFFSET 0 ROWS FETCH NEXT 20 ROWS ONLY
            if (!preg_match('/LIMIT[\s0-9]+|FIRST[\s0-9]+|SKIP[\s0-9]+|OFFSET[\s0-9]+|NEXT[\s0-9]+|HAVING SUM/i', $this->queryString) && preg_match('/SELECT (.*) FROM (.*)/i', $this->queryString, $out)) {
                // will send a SELECT COUNT query
                $useSelectCount = true;

                $numRowsQueryString = '*';
                if ($this->queryType === 'SELECT') {
                    $numRowsQueryString = '';
                    // register the COUNT(DISTINCT) values for numRows
                    if ($this->parameters->get('selectDistinct')) {
                        $numRowsQueryString .= 'DISTINCT ';
                    }

                    $numRowsQueryString .= $this->fields;
                } elseif ($this->queryType === 'RAW') {
                    $numRowsQueryString = $out[1];
                }

                $sql = 'SELECT COUNT(' . $numRowsQueryString . ') AS "row_count" FROM ' . $out[2];

                // Remove the ORDER BY clause
                if (preg_match('/(.*) ORDER BY (?:.*)$/i', $sql, $out)) {
                    $sql = $out[1];
                }
            }

            $numRowsQuery = $pdo->prepare($sql);

            // If there are placeholders...
            foreach ($this->placeholders as $field => $value) {
                // Determine the datatype
                $dataType = Utilities::getDataType($value);

                // Bind the placeholder and value to the query
                $numRowsQuery->bindValue($field, $value, $dataType);
            }

            // Start a timer
            $timeStart = microtime(true);

            // Execute the query
            $numRowsQuery->execute();

            if ($useSelectCount) {
                $row = $numRowsQuery->fetch(PDO::FETCH_OBJ);

                // Find out how long the query took
                $timeEnd = microtime(true);
                $time = $timeEnd - $timeStart;

                // if $row is an object and has the row_count property
                if (is_object($row) && isset($row->row_count)) {
                    $return = $row->row_count;
                }
            } else {
                $rows = $numRowsQuery->fetchAll(PDO::FETCH_OBJ);

                // Find out how long the query took
                $timeEnd = microtime(true);
                $time = $timeEnd - $timeStart;

                if ($rows) {
                    $return = count($rows);
                }
            }

            // Output debug information
            $this->dumpDebug($numRowsQuery, $time);
        } catch (PDOException $pdoException) { // If there was a database error...
            // interpolate the query
            $interpolatedSql = Utilities::interpolateQuery($sql, $this->placeholders);
            // Get the error
            $errorMessage = 'Database Error (' . __METHOD__ . '):<br>' . $pdoException->getMessage() . '<br>' . $interpolatedSql;

            // Output debug information
            $this->dumpDebug($numRowsQuery, null, $errorMessage);

            throw new QueryBuilderException($errorMessage);
        }

        return $return;
    }

    /**
     * Sets the debug mode for the query builder.
     *
     * @param  bool|string $mode The debug mode to set.
     * @return self The current instance of the QueryBuilder.
     */
    public function debugOnce($mode): self
    {
        // accept only false, true, 'silent'
        if (!in_array($mode, [false, true, 'silent'])) {
            throw new InvalidArgumentException('Invalid debug mode');
        }

        $this->debugOnceMode = $mode;
        return $this;
    }

    /**
     * Get the debug view for the query builder.
     *
     * @return View The debug view.
     */
    public function getDebug(): View
    {
        return $this->debugger->getView();
    }

    /**
     * Sets the debug mode for the query builder.
     *
     * @param bool|string $mode The debug mode to set.
     */
    public function setDebug($mode): void
    {
        // accept only false, true, 'silent'
        if (!in_array($mode, [false, true, 'silent'])) {
            throw new InvalidArgumentException('Invalid debug mode');
        }

        $this->debugGlobalMode = $mode;
    }

    /**
     * Get the debug mode value.
     *
     * @return bool|string The debug mode value.
     */
    public function getDebugMode()
    {
        return $this->debugGlobalMode;
    }

    /**
     * Binds placeholders values to a prepared statement.
     *
     * @param PDOStatement $pdoStatement The prepared statement to bind values to.
     * @return PDOStatement The prepared statement with bound values.
     */
    private function bindValues(PDOStatement $pdoStatement): PDOStatement
    {
        $index = 1; // Start index for unnamed parameters
        foreach ($this->placeholders as $key => $value) {
            // Determine the datatype
            $dataType = Utilities::getDataType($value);

            // Check if we are dealing with named or unnamed parameters
            if (is_string($key)) {
                // Bind the placeholder and value to the query
                $pdoStatement->bindValue($key, $value, $dataType);
            } else {
                // Bind the value to the query at the current index for unnamed parameters
                $pdoStatement->bindValue($index, $value, $dataType);
                ++$index;
            }
        }

        return $pdoStatement;
    }

    /**
     * Clears the query builder by resetting all the properties.
     *
     * @access private
     */
    private function clear(): void
    {
        $this->debugOnceMode = false;
        $this->fields        = '';
        $this->from          = '';
        $this->queryType     = '';
        $this->placeholders  = [];

        $this->where->reset();
        $this->parameters->reset();
        $this->result->reset();
    }

    /**
     * Dumps the debug information for the query builder.
     *
     * @param ?PDOStatement  $pdoStatement The PDOStatement object representing the query.
     * @param int|float|null $time         The execution time of the query.
     * @param ?string        $errorMessage The error message, if any.
     */
    private function dumpDebug(
        ?PDOStatement $pdoStatement,
        $time,
        ?string $errorMessage = null
    ): void {
        $activeDebugMode = $this->debugOnceMode !== false ? $this->debugOnceMode : $this->debugGlobalMode;
        if ($activeDebugMode !== false) {
            $interpolatedSql = '';

            if ($pdoStatement instanceof PDOStatement) {
                $interpolatedSql = Utilities::interpolateQuery($pdoStatement->queryString, $this->placeholders);
            }

            $view = new View();
            $this->debugger = new Debugger($view);
            $this->debugger->dump($this->queryType, $this->placeholders, $pdoStatement, $interpolatedSql, $time, $errorMessage);

            if ($activeDebugMode === true && !defined('PHPUNIT_TESTSUITE_RUNNIG')) {
                $this->debugger->getView()->render()->clear();
            }
        }
    }

    /**
     * Returns the SQL query for the SELECT statement.
     *
     * @return string The SQL query for the SELECT statement.
     */
    private function getSqlForSelect(): string
    {
        $params = (object) $this->parameters->getAll();

        $sql = (object) [
            'select'    => 'SELECT ',
            'distinct'  => '',
            'fields'    => trim($this->fields),
            'from'      => ' FROM ' . trim($this->from),
            'where'     => '',
            'groupBy'   => '',
            'orderBy'   => '',
            'limit'     => ''
        ];

        // If the selectDistinct property is true
        if ($params->selectDistinct) {
            $sql->distinct = 'DISTINCT ';
        }

        $sql->where = $this->where->getSql();

        // If the groupBy property is set
        if ($params->groupBy) {
            $sql->groupBy = ' GROUP BY ' . $params->groupBy;
        }

        // If the orderBy property is set
        if ($params->orderBy) {
            $sql->orderBy = ' ORDER BY ' . $params->orderBy;
        }

        // If the limit property is set
        if ($params->limit) {
            $sql->limit = $this->connection->getLimitSql((string) $params->limit);
        }

        if ($this->connection->getDriver() === 'firebird') {
            return implode(
                '',
                [
                $sql->select,
                $sql->limit,
                $sql->distinct,
                $sql->fields,
                $sql->from,
                $sql->where,
                $sql->groupBy,
                $sql->orderBy
                ]
            );
        }

        return implode(
            '',
            [
            $sql->select,
            $sql->distinct,
            $sql->fields,
            $sql->from,
            $sql->where,
            $sql->groupBy,
            $sql->orderBy,
            $sql->limit
            ]
        );
    }

    /**
     * Get the SQL statement.
     *
     * @return string The SQL statement.
     */
    private function getSqlStatement(): string
    {
        switch ($this->queryType) {
            case 'INSERT':
                return $this->getSqlForInsert();

            case 'UPDATE':
                return $this->getSqlForUpdate();

            case 'DELETE':
                return $this->getSqlForDelete();

            default:
                throw new QueryBuilderException('Invalid query type');
        }
    }

    /**
     * Returns the SQL query string for an INSERT statement.
     *
     * @return string The SQL query string.
     */
    private function getSqlForInsert(): string
    {
        return 'INSERT INTO ' . trim($this->table) . ' ('
            . implode(', ', array_keys($this->values)) . ') VALUES ('
            . implode(', ', (array) preg_replace('/^([A-Za-z0-9_-]+)$/', ':${1}', array_keys($this->values)))
            . ')';
    }

    /**
     * Returns the SQL query string for an UPDATE statement.
     *
     * @return string The SQL query string.
     */
    private function getSqlForUpdate(): string
    {
        // Create the initial SQL
        $sql = 'UPDATE ' . trim($this->table) . ' SET ';

        // Create SQL SET values
        $output = [];
        foreach (array_keys($this->values) as $key) {
            $output[] = $key . ' = :' . $key;
        }

        // Concatenate the array values
        $sql .= implode(', ', $output);

        return $sql . $this->where->getSql();
    }

    private function getSqlForDelete(): string
    {
        return $this->connection->getDeleteSql($this->table, $this->where->getSql());
    }
}
