<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Result;

use PDOStatement;
use PDO;

class Result
{
    private ?PDOStatement $pdoStatement = null;

    /**
     * Sets the PDOStatement result set.
     *
     * @param PDOStatement $pdoStatement The PDOStatement result set.
     */
    public function set(PDOStatement $pdoStatement): void
    {
        $this->pdoStatement = $pdoStatement;
    }

    /**
     * Resets the result object to its initial state.
     *
     * This method clears any stored result data and resets the internal pointer to the first row.
     * After calling this method, the result object can be reused to execute another query.
     */
    public function reset(): void
    {
        $this->pdoStatement = null;
    }

    /**
     * Destructor: closes the cursor if a statement is still holding an open result set.
     *
     * Without this, a PDO_OCI statement whose cursor is still open (rows pending) is
     * implicitly freed during PHP shutdown, which can trigger an access violation
     * (php-cgi.exe crash, 0xc0000005 in KERNELBASE.dll on Windows) depending on the
     * object destruction order in the host application. Closing the cursor explicitly
     * here converts that implicit free into a clean, deterministic release.
     */
    public function __destruct()
    {
        if ($this->pdoStatement instanceof PDOStatement) {
            try {
                $this->pdoStatement->closeCursor();
            } catch (\Throwable $e) {
                // Never let a cursor close failure break the shutdown sequence
            }
            $this->pdoStatement = null;
        }
    }

    /**
     * Fetches the next row from a result set and returns it according to the $fetch_parameters format
     *
     * @param int $fetch_parameters The PDO fetch style record options
     * @return mixed The next row of the result set or false if we reached the end
     */
    public function fetch(int $fetch_parameters = PDO::FETCH_OBJ)
    {
        if (!$this->pdoStatement instanceof PDOStatement) {
            return false;
        }

        $row = $this->pdoStatement->fetch($fetch_parameters);

        // EOF reached: close the cursor deterministically so the statement
        // no longer holds an open result set (see __destruct for why)
        if ($row === false) {
            try {
                $this->pdoStatement->closeCursor();
            } catch (\Throwable $e) {
                // Ignore: the statement will be released anyway
            }
        }

        return $row;
    }

    /**
     * Fetches all rows from the result set.
     *
     * @param int $fetch_parameters The fetch style to use. Defaults to \PDO::FETCH_OBJ.
     * @return mixed An array containing all rows from the result set, or false on failure.
     */
    public function fetchAll(int $fetch_parameters = PDO::FETCH_OBJ)
    {
        if (!$this->pdoStatement instanceof PDOStatement) {
            return false;
        }

        return $this->pdoStatement->fetchAll($fetch_parameters);
    }
}
