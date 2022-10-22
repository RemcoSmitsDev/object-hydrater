<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class IntType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'int';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}