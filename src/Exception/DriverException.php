<?php

// DriverException class to catch \PDOException exceptions and throw a more meaningful exception
declare(strict_types=1);

namespace Migliori\PowerLitePdo\Exception;

use PDOException;
class DriverException extends DriverManagerException
{
    public function __construct(PDOException $pdoException)
    {
        // If connection was not successful
        $message = 'Database Connection Error: ' . $pdoException->getMessage();

        parent::__construct($message, $pdoException->getCode(), $pdoException);
    }
}
