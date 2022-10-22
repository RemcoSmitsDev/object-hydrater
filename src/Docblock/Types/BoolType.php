<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class BoolType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'bool';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}