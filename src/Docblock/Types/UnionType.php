<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class UnionType extends AbstractType
{
    /** @var AbstractType[] */
    private array $types;

    /** @param AbstractType[] $types */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /** @return AbstractType[] */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getTypeName(): string
    {
        return implode(
            '|',
            array_map(
                static fn(AbstractType $type) => $type->getTypeName(),
                $this->getTypes()
            )
        );
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}