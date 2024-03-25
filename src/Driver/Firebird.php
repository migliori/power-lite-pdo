<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Driver;

use PDO;
use Exception;
use PDOException;
use Migliori\PowerLitePdo\Exception\DriverException;

/**
 * Represents the Firebird driver for the PDO database class.
 */
class Firebird extends DriverBase
{
    protected ?PDO $pdo = null;

    protected string $driver = 'firebird';

    /**
     * Connects to the firebird database using the provided parameters.
     *
     * @param array<string, string> $dsnParams The parameters for the DSN connection string.
     * @param string $username The username for the database connection.
     * @param string $password The password for the database connection.
     * @return Firebird The Firebird object representing the database driver.
     */
    public function connect(array $dsnParams, string $username, string $password): self
    {
        if ($this->pdo instanceof PDO) {
            return $this;
        }

        if (!in_array('firebird', PDO::getAvailableDrivers(), true)) {
            throw new Exception('The PDO Firebird driver is not available');
        }

        $dsn = 'firebird:';
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

            // Force column names uppercase
            $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
        } catch (PDOException $pdoException) {
            // Throw the appropriate exception
            throw new DriverException($pdoException);
        }

        return $this;
    }

    /**
     * Checks if the Firebird driver supports the last insert ID feature.
     *
     * @return bool Returns true if the driver supports last insert ID, false otherwise.
     */
    public function driverSupportsLastInsertId(): bool
    {
        return false;
    }

    /**
     * Returns the SQL query to retrieve the columns of a table in the Firebird database.
     *
     * @param string $table The name of the table.
     * @return string The SQL query to retrieve the columns of the table.
     */
    public function getGetColumnsSql(string $table): string
    {
        if (!$this->pdo instanceof PDO) {
            throw new Exception('The database connection is not established');
        }

        return 'SELECT
        TRIM(R.RDB$FIELD_NAME) AS FIELD_NAME,
        TRIM(R.RDB$DEFAULT_VALUE) AS DEFAULT_VALUE,
        TRIM(R.RDB$NULL_FLAG) AS NULL_FLAG,
        TRIM(DECODE(R.RDB$IDENTITY_TYPE, 0, \'ALWAYS\', 1, \'DEFAULT\', \'UNKNOWN\')) AS IDENTITY_TYPE,
        TRIM(F.RDB$FIELD_LENGTH / RCS.RDB$BYTES_PER_CHARACTER) AS FIELD_LENGTH,
        TRIM(F.RDB$FIELD_PRECISION) AS FIELD_PRECISION,
        TRIM(F.RDB$FIELD_SCALE) AS FIELD_SCALE,
        TRIM(CASE F.RDB$FIELD_TYPE
            WHEN 7 THEN \'SMALLINT\'
            WHEN 8 THEN \'INTEGER\'
            WHEN 10 THEN \'FLOAT\'
            WHEN 12 THEN \'DATE\'
            WHEN 13 THEN \'TIME\'
            WHEN 14 THEN \'CHAR\'
            WHEN 16 THEN \'BIGINT\'
            WHEN 27 THEN \'DOUBLE\'
            WHEN 35 THEN \'TIMESTAMP\'
            WHEN 37 THEN \'VARCHAR\'
            WHEN 261 THEN \'BLOB\'
            ELSE \'UNKNOWN\'
        END) AS FIELD_TYPE,
        TRIM(F.RDB$FIELD_SUB_TYPE) AS FIELD_SUB_TYPE
    FROM
        RDB$FIELDS F
        LEFT JOIN RDB$RELATION_FIELDS R ON R.RDB$FIELD_SOURCE = F.RDB$FIELD_NAME
        LEFT JOIN RDB$CHARACTER_SETS RCS ON RCS.RDB$CHARACTER_SET_ID = F.RDB$CHARACTER_SET_ID
    WHERE RDB$RELATION_NAME = ' . trim(\strtoupper($table)) . ' ORDER BY R.RDB$FIELD_POSITION';
    }

    /**
     * Generates the SQL statement for deleting records from a table based on a given condition.
     *
     * @param string $table The name of the table to delete records from.
     * @param string $where The condition to specify which records to delete.
     * @return string The generated SQL statement for deleting records.
     */
    public function getDeleteSql(string $table, string $where): string
    {
        $sql = 'DELETE FROM ' . trim($table);
        // e.g.: 'film_actor LEFT JOIN film ON film_actor.film_id = film.film_id'
        if (preg_match('/([a-z_-]+) (?>INNER|LEFT|RIGHT) JOIN ([a-z_-]+) ON ([a-z_.-]+)\s*=\s*([a-z_.-]+)/i', $table, $out)) {
            $sql = 'DELETE FROM ' . $out[1] . '
                    WHERE EXISTS (SELECT * FROM ' . $out[2] . ' WHERE ' . $out[4] . ' = ' . $out[3];
            $sql .= str_ireplace('WHERE', 'AND', $where);
            $sql .= ');';
        } else {
            $sql .= $where;
        }

        return $sql;
    }

    /**
     * Returns the name of the column used by the Firebird driver.
     *
     * @return string The name of the column.
     */
    public function getDriverColumnName(): string
    {
        return 'FIELD_NAME';
    }

    /**
     * Returns the SQL query string for retrieving the list of tables in the Firebird database.
     *
     * @return string The SQL query string.
     */
    public function getGetTablesSql(): string
    {
        return 'SELECT TRIM(RDB$RELATION_NAME) FROM RDB$RELATIONS WHERE RDB$VIEW_BLR IS NULL AND (RDB$SYSTEM_FLAG IS NULL OR RDB$SYSTEM_FLAG = 0);';
    }

    /**
     * Returns the SQL query string for applying a limit to the result set, specific to the Firebird driver.
     *
     * @param string $limit The limit value to be applied to the query.
     * @return string The SQL query string with the limit applied.
     */
    public function getLimitSql(string $limit): string
    {
        if (\strpos($limit, ',') === false) {
            return 'FIRST ' . $limit . ' ';
        }

        $limit = str_replace(' ', '', $limit);
        $limitValues = explode(',', $limit);
        return 'FIRST ' . $limitValues[1] . ' SKIP ' . $limitValues[0] . ' ';
    }
}
