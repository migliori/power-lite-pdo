<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo\Query;

use Exception;
use Migliori\PowerLitePdo\Exception\ParametersException;

class Parameters
{
    private bool $selectDistinct  = false;

    private ?string $orderBy      = null;

    private ?string $groupBy      = null;

    /**
     * The limit parameter for the query.
     *
     * @var int|null|string $limit The limit value for the query.
     */
    private $limit;

    /**
     * Add a parameter to the query.
     *
     * @param string $name The name of the parameter.
     * @param bool|int|string $value The value of the parameter.
     * @return $this The current instance of the Parameters object.
     * @throws ParametersException If the property does not exist.
     */
    public function set(string $name, $value): self
    {
        // if the property doesn't exist throw an exception
        if (!property_exists($this, $name)) {
            throw new ParametersException(sprintf('The property %s does not exist', $name));
        }

        $this->$name = $value;
        return $this;
    }

    /**
     * Resets the parameters array.
     *
     * This method clears all the parameters stored in the array.
     */
    public function reset(): void
    {
        $this->selectDistinct = false;
        $this->orderBy = null;
        $this->groupBy = null;
        $this->limit = null;
    }

    /**
     * Get the value of a parameter by name.
     *
     * @param string $name The name of the parameter.
     * @return mixed The value of the parameter.
     */
    public function get(string $name)
    {
        if (!property_exists($this, $name)) {
            throw new Exception(sprintf('The property %s does not exist', $name));
        }

        return $this->$name;
    }

    /**
     * Get the parameters for the query.
     *
     * @return array<string, mixed> The parameters for the query.
     */
    public function getAll(): array
    {
        return get_object_vars($this);
    }
}
