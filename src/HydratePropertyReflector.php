<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Exception\AbstractHydrateException;
use RemcoSmits\Hydrate\Exception\ClassDoesntExistsException;
use RemcoSmits\Hydrate\Exception\HydrateFailedException;
use RemcoSmits\Hydrate\Exception\InvalidDataTypeException;
use RemcoSmits\Hydrate\Exception\ValueWasNotFoundException;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionProperty;
use Throwable;

final class HydratePropertyReflector
{
    private HydrateReflectionProperty $property;

    private object $classInstance;

    private PropertyValue $value;

    private function __construct(object $classInstance, HydrateReflectionProperty $property, PropertyValue $value)
    {
        $this->classInstance = $classInstance;
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ReflectionException
     * @throws ValueWasNotFoundException
     */
    public static function from(object $classInstance, HydrateReflectionProperty $property, PropertyValue $value): void
    {
        $reflector = new self($classInstance, $property, $value);

        $reflector->setPropertyValue();
    }

    /**
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ReflectionException
     * @throws ValueWasNotFoundException
     */
    public function setPropertyValue(): void
    {
        if ($this->property->isStatic()) {
            return;
        }

        $type = $this->property->getPropertyType();

        if ($this->value->wasFound() === false && $type->allowsNull() === false) {
            throw new ValueWasNotFoundException($this->property);
        }

        if ($type->getType() === 'mixed' || ($this->value->wasFound() === false && $type->allowsNull())) {
            $this->property->setValue(
                $this->classInstance,
                $this->value->getValue()
            );

            return;
        }

        // TODO: check what to do when is value is diff type then docblock
        if ($type->isBuildIn() && $type->isCollection() === false) {
            $this->property->setValue(
                $this->classInstance,
                $this->getValueWithCorrectType($type, $this->value->getValue())
            );

            return;
        }

        if ($type->isCollection()) {
            $this->property->setValue(
                $this->classInstance,
                $this->hydrateCollection($type)
            );

            return;
        }

        $this->property->setValue(
            $this->classInstance,
            Hydrater::to(
                $type->getFqn(),
                $this->value->getValue()
            )
        );
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws InvalidDataTypeException
     */
    private function getValueWithCorrectType(PropertyType $propertyType, $value)
    {
        $scalarTypes = ['int', 'integer', 'float', 'double', 'string', 'bool', 'boolean'];

        $type = $propertyType->getCollectionItemType() ?? $propertyType->getType();

        if (is_scalar($value) === false && in_array($type, $scalarTypes)) {
            try {
                return $this->property->getValue($this->classInstance);
            } catch (Throwable $throwable) {
                throw new InvalidDataTypeException($propertyType->getFqn(), $value);
            }
        }

        if (in_array($type, PropertyType::BUILD_IN_TYPES)) {
            settype($value, $type);

            return $value;
        }

        $class = $propertyType->getFqn();

        return new $class($value);
    }

    /**
     * @param PropertyType $propertyType
     *
     * @return array<int, mixed>|object
     *
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ReflectionException
     * @throws ValueWasNotFoundException
     */
    private function hydrateCollection(PropertyType $propertyType)
    {
        $data = $this->hydrateArray();

        if ($propertyType->getType() === 'array') {
            return $data;
        }

        $class = $propertyType->getFqn();

        return new $class($data);
    }


    /**
     * @return array<int, mixed>
     *
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ReflectionException
     * @throws ValueWasNotFoundException
     */
    private function hydrateArray(): array
    {
        $this->throwWhenIsNotAnArray(
            $value = $this->value->getValue()
        );

        $fqn = $this->findFqn($this->property);

        $array = [];

        $collectionItemType = $this->property->getPropertyType()->getCollectionItemType();

        foreach ($value as $key => $item) {
            // when is array<int>|array<string>|string[]|int[]|Collection<int>|Collection<string>
            // format item to string|int
            if (in_array($collectionItemType, PropertyType::BUILD_IN_TYPES, true)) {
                $array[$key] = $this->getValueWithCorrectType($this->property->getPropertyType(), $item);

                continue;
            }

            $this->throwWhenIsNotAnArray($item);

            $array[$key] = Hydrater::to($fqn, $item);
        }

        return $array;
    }

    /**
     * @param mixed $value
     *
     * @throws InvalidDataTypeException
     */
    private function throwWhenIsNotAnArray($value): void
    {
        if (is_array($value) === false) {
            throw new InvalidDataTypeException('array', $value);
        }
    }

    /**
     * @param HydrateReflectionProperty $property
     *
     * @return class-string
     *
     * @throws \RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException
     * @throws ReflectionException
     */
    private function findFqn(HydrateReflectionProperty $property): string
    {
        $type = $property->getPropertyType()->getCollectionItemType() ?? $property->getPropertyType()->getFqn();

        if (class_exists($type) || in_array($type, PropertyType::BUILD_IN_TYPES)) {
            return $type;
        }

        $contextFactory = new ContextFactory();
        $context = $contextFactory->createFromReflector($property->getProperty());

        $typeResolver = new TypeResolver();

        try {
            return (string)$typeResolver->resolve($type, $context);
        } catch (Throwable $throwable) {
            return $type;
        }
    }
}
