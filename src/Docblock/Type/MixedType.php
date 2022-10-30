<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type;

final class MixedType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'mixed';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}