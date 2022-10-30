<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;

final class HydrateReflectionClass
{
    /** @var HydrateReflectionProperty[] */
    private array $properties;

    private string $name;

    private string $filename;

    /**
     * @param class-string $class
     * @throws ReflectionException
     * @throws FailedToParseDocblockToTypeException
     */
    public function __construct(string $class)
    {
        $reflection = new ReflectionClass($class);

        $this->name = $reflection->getName();

        $this->filename = $reflection->getFileName();

        $this->properties = array_map(
            static fn(ReflectionProperty $property) => new HydrateReflectionProperty($property),
            $reflection->getProperties()
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    /** @return HydrateReflectionProperty[] */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
