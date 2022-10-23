<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Exception;

final class FailedToMapTypeException extends AbstractDocblockParserException
{
    public function __construct(string $type)
    {
        parent::__construct(
            sprintf('Failed to map type [%s]', $type)
        );
    }
}