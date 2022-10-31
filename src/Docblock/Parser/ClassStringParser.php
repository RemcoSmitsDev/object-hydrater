<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;
use RemcoSmits\Hydrate\Docblock\Type\ClassStringType;

final class ClassStringParser extends AbstractParser
{
    /** @inheritDoc */
    public function parse(string $typeString, array $matches): AbstractType
    {
        if (empty($matches)) {
            throw new InvalidTypeFormatException();
        }

        return new ClassStringType($matches['classStringOfObject'] ?? null);
    }

    public function matchFormat(): string
    {
        return '/^class-string(?:\<(?<classStringOfObject>[A-z]+)\>)*$/';
    }
}