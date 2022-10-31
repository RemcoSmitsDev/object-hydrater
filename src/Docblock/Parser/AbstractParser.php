<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;

abstract class AbstractParser
{
    /**
     * Return the regex format that the type supports
     */
    abstract public function matchFormat(): string;

    /**
     * @param array<string|int, string|null> $matches
     *
     * @throws InvalidTypeFormatException
     */
    abstract public function parse(string $typeString, array $matches): AbstractType;
}