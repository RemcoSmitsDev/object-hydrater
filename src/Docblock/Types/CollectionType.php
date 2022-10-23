<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

final class CollectionType extends AbstractType
{
    /** @var StringType|IntType|UnionType<StringType|IntType> */
    private AbstractType $keyType;

    private string $collectionTypeName;

    private ?AbstractType $subType;

    /** @param StringType|IntType|UnionType $keyType */
    public function __construct(
        string $collectionTypeName,
        AbstractType $keyType,
        ?AbstractType $subType = null
    ) {
        $this->keyType = $keyType;
        $this->collectionTypeName = $collectionTypeName;
        $this->subType = $subType;
    }

    public function getTypeName(): string
    {
        return $this->collectionTypeName;
    }

    /** @return StringType|IntType|UnionType */
    public function getKeyTypes(): AbstractType
    {
        return $this->keyType;
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