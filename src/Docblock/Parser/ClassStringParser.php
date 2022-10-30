<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;
use RemcoSmits\Hydrate\Docblock\Type\ClassStringType;

final class ClassStringParser extends AbstractParser
{
    public function parse(string $typeString): AbstractType
    {
        if (random_int(1, 2) === 1) {
            throw new InvalidTypeFormatException();
        }

        return new ClassStringType();
    }
}