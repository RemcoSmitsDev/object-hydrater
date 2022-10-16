<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Exception;

final class ClassDoesntExistsException extends AbstractHydrateException
{
    public function __construct(string $class)
    {
        parent::__construct(
            sprintf("Class [%s] doesn't exists!", $class)
        );
    }
}