<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Driver;

use PDO;
use Exception;
use PDOException;
use Migliori\PowerLitePdo\Exception\DriverException;

/**
 * Represents the Oracle driver for the PDO database class.
 */
class Oci extends DriverBase
{
    protected ?PDO $pdo = null;

    protected string $driver = 'oci';

    /**
     * Connects to the Oracle database using the provided parameters.
     *
     * @param array<string, string> $dsnParams The parameters for the DSN connection string.
     * @param string $username The username for the database connection.
     * @param string $password The password for the database connection.
     * @return Oci The Oci object representing the database driver.
     */
    public function connect(array $dsnParams, string $username, string $password): self
    {
        if ($this->pdo instanceof PDO) {
            return $this;
        }

        if (!in_array('oci', PDO::getAvailableDrivers(), true)) {
            throw new Exception('The PDO OCI driver is not available');
        }

        $dsn = 'oci:';
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

            // standardize the OCI date format
            $sql = "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'";
            $this->pdo->exec($sql);
        } catch (PDOException $pdoException) {
            // Throw the appropriate exception
            throw new DriverException($pdoException);
        }

        return $this;
    }

    /**
     * Checks if the OCI driver supports the last insert ID feature.
     *
     * @return bool Returns true if the OCI driver supports last insert ID, false otherwise.
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

        return 'SELECT * FROM USER_TAB_COLUMNS WHERE TABLE_NAME = ' . trim($table);
    }

    /**
     * Generates the SQL statement for deleting records from a table based on a given condition.
     *
     * @param string $table The name of the table from which to delete records.
     * @param string $where The condition used to determine which records to delete.
     * @return string The generated SQL statement for deleting records.
     */
    public function getDeleteSql(string $table, string $where): string
    {
        $sql = 'DELETE FROM ' . trim($table);
        // e.g.: 'film_actor LEFT JOIN film ON film_actor.film_id = film.film_id'
        if (preg_match('/([a-z_-]+) (?>INNER|LEFT|RIGHT) JOIN ([a-z_-]+) ON ([a-z_.-]+)\s*=\s*([a-z_.-]+)/i', $table, $out)) {
            $sql = 'DELETE ' . $out[1] . '
                    WHERE EXISTS (SELECT * FROM ' . $out[2] . ' WHERE ' . $out[4] . ' = ' . $out[3];
            $sql .= str_ireplace('WHERE', 'AND', $where);
            $sql .= ')';
        } else {
            $sql .= $where;
        }

        return $sql;
    }

    /**
     * Returns the name of the driver column.
     *
     * @return string The name of the driver column.
     */
    public function getDriverColumnName(): string
    {
        return 'COLUMN_NAME';
    }

    /**
     * Returns the SQL query to retrieve the list of tables in the Oracle database.
     *
     * @return string The SQL query to retrieve the list of tables.
     */
    public function getGetTablesSql(): string
    {
        return 'SELECT * FROM user_tables ORDER BY table_name';
    }

    /**
     * Returns the SQL query string for applying a limit to a query for the Oracle OCI driver.
     *
     * @param string $limit The limit value.
     * @return string The SQL query string with the applied limit.
     */
    public function getLimitSql(string $limit): string
    {
        if (\strpos($limit, ',') === false) {
            return ' FETCH NEXT ' . $limit . ' ROWS ONLY';
        }

        $limit = str_replace(' ', '', $limit);
        $limitValues = explode(',', $limit);
        return ' OFFSET ' . $limitValues[0] . ' ROWS FETCH NEXT ' . $limitValues[1] . ' ROWS ONLY';
    }
}
