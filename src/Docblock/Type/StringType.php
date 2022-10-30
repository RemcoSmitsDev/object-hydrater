<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type;

final class StringType extends AbstractType
{
    public function getTypeName(): string
    {
        return 'string';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}