<?php

namespace RemcoSmits\Hydrate\Tests\Unit\TestClasses;

use ArrayIterator;
use DateTimeImmutable;

class TestClass
{
    public static bool $skip = true;

    public string $name;

    public string $defaultValue = 'default string';

    public SubClass $subClass;

    public ?SubClass $altSub;

    /** @var Collection<SubClass> */
    public Collection $collection;

    /** @var SubClass[] */
    public array $items;

    /** @var SubClass[] */
    public $propWithoutDeclaredType;

    public DateTimeImmutable $date;

    /** @var ArrayIterator<SubClass> */
    public ArrayIterator $iterator;

    /** @var array<int> */
    public array $array_of_ints = [];

    /** @var ArrayIterator<int> */
    public ArrayIterator $array_iterator_ints;

    /** @var ArrayIterator<string> */
    public ArrayIterator $array_iterator_strings;

    public $without_any_type;

    /** @return Collection<SubClass> */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubClass(): SubClass
    {
        return $this->subClass;
    }

    public function getAltSub(): ?SubClass
    {
        return $this->altSub;
    }

    /** @return SubClass[] */
    public function getItems(): array
    {
        return $this->items;
    }

    /** @return SubClass[] */
    public function getPropWithoutDeclaredType(): array
    {
        return $this->propWithoutDeclaredType;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /** @return ArrayIterator<SubClass> */
    public function getIterator(): ArrayIterator
    {
        return $this->iterator;
    }

    /** @return array<int> */
    public function getArrayOfInts(): array
    {
        return $this->array_of_ints;
    }
}
