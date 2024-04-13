<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Driver;

use PDO;
use Exception;
use PDOException;
use Migliori\PowerLitePdo\Exception\DriverException;

/**
 * Represents the MySQL driver for the PDO database class.
 */
class Mysql extends DriverBase
{
    protected ?PDO $pdo = null;

    protected string $driver = 'mysql';

    /**
     * Connects to the MySQL database using the provided parameters.
     *
     * @param array<string, string> $dsnParams The parameters for the DSN connection string.
     * @param string $username The username for the database connection.
     * @param string $password The password for the database connection.
     * @return Mysql The Mysql object representing the database driver.
     */
    public function connect(array $dsnParams, string $username, string $password): self
    {
        if ($this->pdo instanceof PDO) {
            return $this;
        }

        if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
            throw new Exception('The PDO MySQL driver is not available');
        }

        $this->dsnParams = $dsnParams;
        $this->username = $username;
        $this->password = $password;

        $dsn = 'mysql:';
        if (!empty($dsnParams['host'])) {
            $dsn .= 'host=' . $dsnParams['host'] . ';';
        }

        if (!empty($dsnParams['port'])) {
            $dsn .= 'port=' . $dsnParams['port'] . ';';
        }

        if (!empty($dsnParams['dbname'])) {
            $dsn .= 'dbname=' . $dsnParams['dbname'] . ';';
        }

        if (!empty($dsnParams['charset'])) {
            $dsn .= 'charset=' . $dsnParams['charset'] . ';';
        }

        try {
            $this->pdo = new PDO(
                $dsn,
                $username,
                $password
            );

            // Set the PDO error mode to exception
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $pdoException) {
            // Throw the appropriate exception
            throw new DriverException($pdoException);
        }

        return $this;
    }

    /**
     * Checks if the MySQL driver supports the last insert ID feature.
     *
     * @return bool Returns true if the driver supports last insert ID, false otherwise.
     */
    public function driverSupportsLastInsertId(): bool
    {
        return true;
    }

    /**
     * Returns the SQL query to retrieve the columns of a table.
     *
     * @param string $table The name of the table.
     * @return string The SQL query to retrieve the columns of the table.
     */
    public function getGetColumnsSql(string $table): string
    {
        if (!$this->pdo instanceof PDO) {
            throw new Exception('The database connection is not established');
        }

        return 'SHOW COLUMNS FROM ' . trim($table);
    }

    /**
     * Generates a DELETE SQL statement for the specified table and WHERE clause.
     *
     * @param string $table The name of the table.
     * @param string $where The WHERE clause of the SQL statement.
     * @return string The generated DELETE SQL statement.
     */
    public function getDeleteSql(string $table, string $where): string
    {
        $table_src = '';
        if (preg_match('/([a-z_-]+) (?>INNER|LEFT|RIGHT) JOIN/i', $table, $out)) {
            $table_src = $out[1] . ' ';
        }

        return 'DELETE ' . $table_src . 'FROM ' . trim($table) . $where;
    }

    /**
     * Returns the name of the driver column.
     *
     * @return string The name of the driver column.
     */
    public function getDriverColumnName(): string
    {
        return 'Field';
    }

    /**
     * Returns the SQL query string for retrieving the list of tables in the MySQL database.
     *
     * @return string The SQL query string.
     */
    public function getGetTablesSql(): string
    {
        return "show full tables where Table_Type != 'VIEW'";
    }

    /**
     * Returns the SQL query string for applying a limit to the result set.
     *
     * @param string $limit The limit value.
     * @return string The SQL query string with the limit applied.
     */
    public function getLimitSql(string $limit): string
    {
        return ' LIMIT ' . $limit;
    }
}
