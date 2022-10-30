<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type;

final class ClassStringType extends AbstractType
{
    private ?string $type;

    public function __construct(?string $type = null)
    {
        $this->type = $type;
    }

    public function getTypeName(): string
    {
        return 'class-string';
    }

    public function isCollectionType(): bool
    {
        return false;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}