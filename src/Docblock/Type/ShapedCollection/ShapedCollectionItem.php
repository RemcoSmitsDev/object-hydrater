<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Type\ShapedCollection;

use RemcoSmits\Hydrate\Docblock\Type\AbstractType;

final class ShapedCollectionItem
{
    /** @var string|int */
    private $key;

    private bool $isOptional;

    private AbstractType $type;

    /**
     * @param string|int $key
     */
    public function __construct($key, bool $isOptional, AbstractType $type)
    {
        $this->key = $key;
        $this->isOptional = $isOptional;
        $this->type = $type;
    }

    /** @return int|string */
    public function getKey()
    {
        return $this->key;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function getType(): AbstractType
    {
        return $this->type;
    }
}