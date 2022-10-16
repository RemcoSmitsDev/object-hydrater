<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate;

final class PropertyValue
{
    /** @var mixed */
    private $value;

    private bool $valueWasFound;

    /** @param mixed $value */
    public function __construct(
        $value,
        bool $valueWasFound
    ) {
        $this->value = $value;
        $this->valueWasFound = $valueWasFound;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function wasFound(): bool
    {
        return $this->valueWasFound;
    }
}