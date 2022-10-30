<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;

abstract class AbstractParser
{
    /** @throws InvalidTypeFormatException */
    abstract public function parse(string $typeString): AbstractType;
}