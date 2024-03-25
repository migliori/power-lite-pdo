<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Driver;

use PDO;
use Exception;
use PDOException;
use Migliori\PowerLitePdo\Exception\DriverException;

/**
 * Represents the PostgreSQL driver for the PDO database class.
 */
class Pgsql extends DriverBase
{
    protected ?PDO $pdo = null;

    protected string $driver = 'pgsql';

    /**
     * Connects to the PostgreSQL database using the provided parameters.
     *
     * @param array<string, string> $dsnParams The parameters for the DSN connection string.
     * @param string $username The username for the database connection.
     * @param string $password The password for the database connection.
     * @return Pgsql The Pgsql object representing the database driver.
     */
    public function connect(array $dsnParams, string $username, string $password): self
    {
        if ($this->pdo instanceof PDO) {
            return $this;
        }

        if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
            throw new Exception('The PDO PGSQL driver is not available');
        }

        $dsn = 'pgsql:';
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
     * Checks if the PostgreSQL driver supports the last insert ID retrieval.
     *
     * @return bool Returns true if the driver supports last insert ID retrieval, false otherwise.
     */
    public function driverSupportsLastInsertId(): bool
    {
        return false;
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

        return 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ' . trim($table) . ' ORDER BY ordinal_position';
    }

    /**
     * Generates a DELETE SQL statement for the specified table and WHERE clause.
     *
     * @param string $table The name of the table to delete from.
     * @param string $where The WHERE clause to filter the rows to be deleted.
     * @return string The generated DELETE SQL statement.
     */
    public function getDeleteSql(string $table, string $where): string
    {
        $sql = 'DELETE FROM ' . trim($table);
        // e.g.: 'film_actor LEFT JOIN film ON film_actor.film_id = film.film_id'
        if (preg_match('/([a-z_-]+) (?>INNER|LEFT|RIGHT) JOIN ([a-z_-]+) ON ([a-z_.-]+)\s*=\s*([a-z_.-]+)/i', $table, $out)) {
            $sql = 'DELETE FROM ' . $out[1] . ' USING ' . $out[2];
        }

        return $sql . $where;
    }

    /**
     * Returns the name of the driver column.
     *
     * @return string The name of the driver column.
     */
    public function getDriverColumnName(): string
    {
        return 'column_name';
    }

    public function getGetTablesSql(): string
    {
        return "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema')";
    }

    /**
     * Returns the SQL query string for applying a limit to the result set in PostgreSQL.
     *
     * @param string $limit The limit value to be applied to the query.
     * @return string The SQL query string with the limit applied.
     */
    public function getLimitSql(string $limit): string
    {
        if (\strpos($limit, ',') === false) {
            return ' LIMIT ' . $limit;
        }

        $limit = str_replace(' ', '', $limit);
        $limitValues = explode(',', $limit);
        return ' LIMIT ' . $limitValues[1] . ' offset ' . $limitValues[0];
    }
}
