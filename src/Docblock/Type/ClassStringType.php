<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type;

final class ClassStringType extends AbstractType
{
    private ?string $classStringOfObject;

    public function __construct(?string $classStringOfObject = null)
    {
        $this->classStringOfObject = $classStringOfObject;
    }

    public function getTypeName(): string
    {
        return 'class-string';
    }

    public function isCollectionType(): bool
    {
        return false;
    }

    public function getClassStringOfObject(): ?string
    {
        return $this->classStringOfObject;
    }
}