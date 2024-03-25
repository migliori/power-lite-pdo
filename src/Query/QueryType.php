<?php

namespace Migliori\PowerLitePdo\Query;

use InvalidArgumentException;
/**
 * Represents the type of a database query.
 */
class QueryType
{
    const RAW = 'RAW';

    const SELECT = 'SELECT';

    const INSERT = 'INSERT';

    const UPDATE = 'UPDATE';

    const DELETE = 'DELETE';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::RAW, self::SELECT, self::INSERT, self::UPDATE, self::DELETE])) {
            throw new InvalidArgumentException('Invalid QueryType');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
