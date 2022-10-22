<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class ScalarType extends AbstractType
{
    /** @var array<int, IntType|StringType|BoolType|FloatType> */
    private array $types;

    public function __construct()
    {
        $this->types = [
            new IntType(),
            new StringType(),
            new BoolType(),
            new FloatType()
        ];
    }

    /** @return array<int, IntType|StringType|BoolType|FloatType> */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getTypeName(): string
    {
        return 'scalar';
    }

    public function isCollectionType(): bool
    {
        return false;
    }
}