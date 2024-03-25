<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Exception;

use Exception;
/**
 * Represents an exception that is thrown by the DriverManager class.
 *
 * This exception is used to indicate errors that occur during the management of database drivers.
 */
class ParametersException extends Exception
{
    /**
     * Constructor.
     *
     * @param string|null $message The exception message.
     * @param int $code The exception code.
     * @param Exception|null $exception The previous exception.
     */
    public function __construct(?string $message = null, int $code = 0, ?Exception $exception = null)
    {
        $file = parent::getFile();
        $line = parent::getLine();
        $message = __CLASS__ . ": Exception thrown in {$file} on line {$line}: [Code {$code}]
        {$message}";

        parent::__construct($message, $code, $exception);
    }
}
