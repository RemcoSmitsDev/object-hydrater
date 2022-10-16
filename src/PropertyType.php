<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

final class PropertyType
{
    public const BUILD_IN_TYPES = [
        "bool",
        "boolean",
        "int",
        "integer",
        "float",
        "double",
        "string",
        "array",
        "object",
        "null"
    ];

    private string $type;

    private bool $isStatic = false;

    private bool $allowNull = false;

    private string $fqn;

    private ?string $collectionItemType;

    /**
     * @param class-string $type
     * @param class-string $fqn
     * @param class-string|null $collectionItemType
     */
    public function __construct(
        string $type,
        string $fqn,
        ?string $collectionItemType = null
    ) {
        $this->type = $type;
        $this->fqn = $fqn;
        $this->collectionItemType = $collectionItemType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isBuildIn(): bool
    {
        if (in_array($this->getType(), self::BUILD_IN_TYPES)) {
            return true;
        }

        try {
            return (new ReflectionClass($this->getFqn()))->isUserDefined() === false;
        } catch (Throwable $throwable) {
            return false;
        }
    }

    public function getFqn(): string
    {
        return $this->fqn;
    }

    public function isCollection(): bool
    {
        return $this->getCollectionItemType() !== null || $this->getType() === 'array';
    }

    public function getCollectionItemType(): ?string
    {
        return $this->collectionItemType;
    }

    public function allowsNull(): bool
    {
        return $this->allowNull;
    }

    public function setAllowNull(bool $allowNull): self
    {
        $this->allowNull = $allowNull;

        return $this;
    }

    /** @param class-string $fqn */
    public function setFqn(string $fqn): self
    {
        $this->fqn = $fqn;

        return $this;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /** @return class-string */
    public static function findFqn(ReflectionProperty $property, string $type): string
    {
        if (class_exists($type) || in_array($type, self::BUILD_IN_TYPES)) {
            return $type;
        }

        $contextFactory = new ContextFactory();
        $context = $contextFactory->createFromReflector($property);

        $typeResolver = new TypeResolver();

        try {
            return (string)$typeResolver->resolve($type, $context);
        } catch (Throwable $throwable) {
            return $type;
        }
    }
}