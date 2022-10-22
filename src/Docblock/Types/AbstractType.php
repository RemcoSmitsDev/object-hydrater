<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

abstract class AbstractType
{
    abstract public function getTypeName(): string;

    abstract public function isCollectionType(): bool;
}