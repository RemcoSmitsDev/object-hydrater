<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate;

use ReflectionException;
use RemcoSmits\Hydrate\Cache\HydraterCache;
use RemcoSmits\Hydrate\Exception\AbstractHydrateException;
use RemcoSmits\Hydrate\Exception\ClassDoesntExistsException;
use RemcoSmits\Hydrate\Exception\HydrateFailedException;
use RemcoSmits\Hydrate\Exception\InvalidDataTypeException;
use RemcoSmits\Hydrate\Exception\ValueWasNotFoundException;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionClass;

final class Hydrater
{
    /**
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     * @param array<string, mixed> $data
     *
     * @return TValue
     *
     * @throws ClassDoesntExistsException
     * @throws AbstractHydrateException
     * @throws InvalidDataTypeException
     * @throws ValueWasNotFoundException
     * @throws HydrateFailedException
     */
    public static function to(string $class, array $data): object
    {
        self::throwIfClassDoesntExists($class);

        try {
            $reflectionClass = self::getHydrateReflectionClass($class);
        } catch (ReflectionException $exception) {
            throw new ClassDoesntExistsException($class);
        }

        return HydrateClassReflector::from($class, $reflectionClass, $data);
    }

    /** @throws ReflectionException */
    private static function getHydrateReflectionClass(string $class): HydrateReflectionClass
    {
        if (HydraterCache::has($class) === false) {
            HydraterCache::set(
                $class,
                $reflectionClass = new HydrateReflectionClass($class)
            );

            return $reflectionClass;
        }

        $reflectionClass = HydraterCache::get($class);

        if ($reflectionClass instanceof HydrateReflectionClass === false || HydraterCache::hasChanged($reflectionClass)
        ) {
            HydraterCache::delete($class);

            return self::getHydrateReflectionClass($class);
        }

        return $reflectionClass;
    }

    /**
     * @param class-string $class
     *
     * @throws ClassDoesntExistsException
     */
    private static function throwIfClassDoesntExists(string $class): void
    {
        if (class_exists($class) === false) {
            throw new ClassDoesntExistsException($class);
        }
    }
}
