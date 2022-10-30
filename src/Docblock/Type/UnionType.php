<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type;

/**
 * @template TValue of AbstractType
 */
final class UnionType extends AbstractType
{
    /** @var TValue[] */
    private array $types;

    /** @param TValue[] $types */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /** @return TValue[] */
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