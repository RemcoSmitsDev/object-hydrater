<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate;

use ReflectionException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Exception\AbstractHydrateException;
use RemcoSmits\Hydrate\Exception\ClassDoesntExistsException;
use RemcoSmits\Hydrate\Exception\HydrateFailedException;
use RemcoSmits\Hydrate\Exception\InvalidDataTypeException;
use RemcoSmits\Hydrate\Exception\ValueWasNotFoundException;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionClass;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionProperty;
use Throwable;

final class HydrateClassReflector
{
    private object $classInstance;

    public function __construct(object $classInstance)
    {
        $this->classInstance = $classInstance;
    }

    /**
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     * @param HydrateReflectionClass $reflectionClass
     * @param array<string, mixed> $data
     *
     * @return TValue
     *
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ValueWasNotFoundException
     * @throws AbstractHydrateException
     */
    public static function from(string $class, HydrateReflectionClass $reflectionClass, array $data): object
    {
        try {
            return (new self(new $class))->setPropertyValues(
                $data,
                $reflectionClass->getProperties()
            )->getClassInstance();
        } catch (AbstractHydrateException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new HydrateFailedException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param HydrateReflectionProperty[] $properties
     *
     * @return HydrateClassReflector
     *
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ReflectionException
     * @throws ValueWasNotFoundException
     */
    private function setPropertyValues(array $data, array $properties): self
    {
        foreach ($properties as $property) {
            try {
                HydratePropertyReflector::from(
                    $this->classInstance,
                    $property,
                    $this->getValueForProperty($data, $property)
                );
            } catch (ValueWasNotFoundException $exception) {
                if ($this->needToThrowWhenValueIsNotFound($exception->getProperty())) {
                    throw $exception;
                }
            }
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     * @throws \RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException
     */
    private function needToThrowWhenValueIsNotFound(HydrateReflectionProperty $property): bool
    {
        if ($property->isInitialized($this->getClassInstance())) {
            return false;
        }

        return $property->getPropertyType()->allowsNull() === false;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws ValueWasNotFoundException
     */
    private function getValueForProperty(array $data, HydrateReflectionProperty $property): PropertyValue
    {
        $name = $property->getName();

        $foundValue = array_key_exists($name, $data);

        if ($foundValue === false && $property->getType() && $property->getType()->allowsNull()) {
            return new PropertyValue(null, false);
        }

        if ($foundValue === false) {
            throw new ValueWasNotFoundException($property);
        }

        return new PropertyValue($data[$name], true);
    }

    public function getClassInstance(): object
    {
        return $this->classInstance;
    }
}
