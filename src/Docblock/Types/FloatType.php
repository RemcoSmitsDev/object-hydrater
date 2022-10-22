<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class FloatType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'float';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}