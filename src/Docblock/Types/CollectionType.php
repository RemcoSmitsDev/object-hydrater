<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class CollectionType extends AbstractType
{
    /** @var array<int, StringType|IntType> */
    private array $collectionKeyTypes;

    private string $collectionTypeName;

    private ?AbstractType $subType;

    /** @param array<int, StringType|IntType> $collectionKeyTypes */
    public function __construct(string $collectionTypeName, array $collectionKeyTypes, ?AbstractType $subType = null)
    {
        $this->collectionKeyTypes = $collectionKeyTypes;

        $this->collectionTypeName = $collectionTypeName;
        $this->subType = $subType;
    }

    public function getTypeName(): string
    {
        return $this->collectionTypeName;
    }

    /** @return array<int, StringType|IntType> */
    public function getCollectionKeyTypes(): array
    {
        return $this->collectionKeyTypes;
    }

    public function isCollectionType(): bool
    {
        return true;
    }

    public function getSubType(): ?AbstractType
    {
        return $this->subType;
    }

    public function setSubType(AbstractType $subType): self
    {
        $this->subType = $subType;

        return $this;
    }
}