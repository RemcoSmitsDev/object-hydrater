<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types;

use RemcoSmits\Hydrate\Docblock\Types\ShapedCollection\ShapedCollectionItem;

final class ShapedCollectionType extends AbstractType
{
    private string $name;

    /** @var ShapedCollectionItem[] */
    private array $shapes;

    /** @param ShapedCollectionItem[] $shapes */
    public function __construct(string $name, array $shapes = [])
    {
        $this->name = $name;
        $this->setShapes($shapes);
    }

    /** @param ShapedCollectionItem[] $shapes */
    public function setShapes(array $shapes): void
    {
        $this->shapes = $shapes;
    }

    public function appendShape(ShapedCollectionItem $shape): void
    {
        $this->shapes[] = $shape;
    }

    public function getShapes(): array
    {
        return $this->shapes;
    }

    public function getTypeName(): string
    {
        return $this->name;
    }

    public function isCollectionType(): bool
    {
        return true;
    }
}