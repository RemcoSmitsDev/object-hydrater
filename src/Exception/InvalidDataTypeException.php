<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Exception;

final class InvalidDataTypeException extends AbstractHydrateException
{
    /** @param mixed $value */
    public function __construct(string $type, $value)
    {
        parent::__construct(
            sprintf('must be a type of %s but is type [%s]!', $type, gettype($value))
        );
    }
}
