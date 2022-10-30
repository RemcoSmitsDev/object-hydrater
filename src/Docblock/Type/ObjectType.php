<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type;

final class ObjectType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'object';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}