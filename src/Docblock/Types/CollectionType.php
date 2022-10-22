<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

class CollectionType extends AbstractType
{
    /**
     * This can be another CollectionType or a string, int enz.
     */
    /** @var AbstractType[] */
    private array $collectionItemTypes;

    private array $collectionKeyTypes;

    private string $collectionTypeName;

    private ?AbstractType $subType;

    public function __construct(
        string $collectionTypeName,
        array $collectionKeyTypes,
        array $collectionItemTypes,
        ?AbstractType $subType = null
    ) {
        // TODO: map this to type objects
        $this->collectionItemTypes = $collectionItemTypes;
        $this->collectionKeyTypes = $collectionKeyTypes;

        $this->collectionTypeName = $collectionTypeName;
        $this->subType = $subType;
    }

    /** @return AbstractType[] */
    public function getCollectionItemTypes(): array
    {
        return $this->collectionItemTypes;
    }

    public function getTypeName(): string
    {
        return $this->collectionTypeName;
    }

    /** @return AbstractType[] */
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