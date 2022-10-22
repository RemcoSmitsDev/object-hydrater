<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Reflection;

use ReflectionException;
use ReflectionProperty;
use RemcoSmits\Hydrate\DocblockParser;
use RemcoSmits\Hydrate\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\PropertyType;

final class HydrateReflectionProperty
{
    private string $name;

    private ?HydrateReflectionNamedType $nativeType = null;

    private PropertyType $propertyType;

    /** @var false|string */
    private $docblock;

    private bool $isStatic;

    private ?bool $isInitialized = null;

    private string $declaringClass;

    private ?ReflectionProperty $property;

    /**
     * @throws ReflectionException
     * @throws FailedToParseDocblockToTypeException
     */
    public function __construct(ReflectionProperty $property)
    {
        $this->property = $property;
        $this->name = $property->getName();
        $this->isStatic = $property->isStatic();

        if ($property->hasType()) {
            $this->nativeType = new HydrateReflectionNamedType($property->getType());
        }

        $this->docblock = $property->getDocComment();
        $this->declaringClass = $property->getDeclaringClass()->getName();

        $this->propertyType = $this->getPropertyType();
    }

    public function getType(): ?HydrateReflectionNamedType
    {
        return $this->nativeType;
    }

    /**
     * @throws ReflectionException
     * @throws FailedToParseDocblockToTypeException
     */
    public function getPropertyType(): PropertyType
    {
        if (isset($this->propertyType)) {
            return $this->propertyType;
        }

        $type = null;

        if ($this->getDocComment()) {
            $type = DocblockParser::getType($this->getProperty()->getDocComment());
            $type->setFqn(
                PropertyType::findFqn($this->getProperty(), $type->getType())
            );
        }

        if ($type === null && $this->getType() instanceof HydrateReflectionNamedType) {
            $type = new PropertyType(
                $this->getType()->getName(),
                PropertyType::findFqn($this->getProperty(), $this->getType()->getName())
            );

            $type->setAllowNull($this->getType()->allowsNull());
        }

        if ($type === null) {
            $type = new PropertyType('mixed', 'mixed');
        }

        return $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDocComment()
    {
        return $this->docblock;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /** @throws ReflectionException */
    public function isInitialized(?object $object = null): bool
    {
        if ($this->isInitialized === null) {
            $this->getProperty()->setAccessible(true);
            $this->isInitialized = $this->getProperty()->isInitialized($object);
        }

        return $this->isInitialized;
    }

    /**
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function getValue(object $instance)
    {
        $this->getProperty()->setAccessible(true);
        return $this->getProperty()->getValue($instance);
    }

    /** @throws ReflectionException */
    public function getProperty(): ReflectionProperty
    {
        return $this->property = $this->property ?? new ReflectionProperty($this->declaringClass, $this->getName());
    }

    /** @throws ReflectionException */
    public function setValue(object $class, $value): void
    {
        $this->getProperty()->setAccessible(true);
        $this->getProperty()->setValue($class, $value);
    }

    /** @return array<int, string> */
    public function __sleep(): array
    {
        return [
            'name',
            'nativeType',
            'docblock',
            'isStatic',
            'isInitialized',
            'declaringClass',
            'propertyType'
        ];
    }
}
