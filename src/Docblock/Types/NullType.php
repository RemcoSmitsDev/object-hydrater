<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class NullType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'null';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}