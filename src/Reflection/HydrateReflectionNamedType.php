<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Reflection;

use ReflectionNamedType;

final class HydrateReflectionNamedType
{
    private string $name;

    private bool $allowsNull;

    public function __construct(ReflectionNamedType $type)
    {
        $this->name = $type->getName();
        $this->allowsNull = $type->allowsNull();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }
}