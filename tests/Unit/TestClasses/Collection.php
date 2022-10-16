<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Tests\Unit\TestClasses;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonException;
use JsonSerializable;

/**
 * @template TValue
 */
class Collection implements Countable, JsonSerializable, IteratorAggregate
{
    /** @var array<int, TValue> */
    private array $items;

    /** @param array<int, TValue> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /** @return array<int, TValue> */
    public function toArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @return ArrayIterator<int, TValue> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /** @throws JsonException */
    public function jsonSerialize(): string
    {
        return json_encode($this->items, JSON_THROW_ON_ERROR);
    }
}