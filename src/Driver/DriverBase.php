<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Driver;

use PDO;
/**
 * This is an abstract class that serves as the base for all database driver's classes.
 * It provides common functionality and properties that are shared among different database drivers.
 */
abstract class DriverBase
{
    protected ?PDO $pdo = null;

    protected string $driver;

    /**
     * @var array<string, string> $dsnParams The array to store the connection details.
     */
    protected array $dsnParams = [];

    protected string $username;

    protected string $password;

    /**
     * Establishes a connection to the database using the provided DSN, username, and password.
     *
     * @param array<string, string> $dsn The data source name.
     * @param string $username The username for the database connection.
     * @param string $password The password for the database connection.
     * @return Mysql|Firebird|Oci|Pgsql The object representing the database driver.
     */
    abstract public function connect(array $dsn, string $username, string $password);

    /**
     * Disconnects from the database.
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Returns the driver name for the connection.
     *
     * @return string The driver name.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Returns the PDO object representing the database connection.
     *
     * @return PDO The PDO object representing the database connection.
     */
    public function getPdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        // try to reconnect to the database if the connection is lost
        return $this->connect($this->dsnParams, $this->username, $this->password)->getPdo();
    }

    /**
     * Determines if the driver supports the last insert ID feature.
     *
     * @return bool True if the driver supports last insert ID, false otherwise.
     */
    abstract public function driverSupportsLastInsertId(): bool;

    /**
     * Returns the SQL query to retrieve the columns of a table.
     *
     * @return string The SQL query to retrieve the columns.
     */
    /**
     * Returns the SQL query to retrieve the columns of a table.
     *
     * @param string $table The name of the table.
     * @return string The SQL query to retrieve the columns of the table.
     */
    abstract public function getGetColumnsSql(string $table): string;

    /**
     * Returns the name of the driver column.
     *
     * @return string The name of the driver column.
     */
    abstract public function getDriverColumnName(): string;

    /**
     * Returns the SQL query to retrieve the list of tables in the database.
     *
     * @return string The SQL query.
     */
    abstract public  function getGetTablesSql(): string;

    /**
     * Retrieves the SQL query for applying a limit to the result set based on the specific driver.
     *
     * @param string $limit The limit to be applied to the result set.
     * @return string The SQL query for applying the limit.
     */
    abstract public function getLimitSql(string $limit): string;


    abstract public function getDeleteSql(string $table, string $where): string;
}
