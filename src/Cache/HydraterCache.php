<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Cache;

use RemcoSmits\Hydrate\Reflection\HydrateReflectionClass;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionNamedType;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionProperty;
use Throwable;

final class HydraterCache
{
    public const CACHE_FOLDER = __DIR__ . '/../../var/';

    /** @param class-string $class */
    public static function get(string $class): ?HydrateReflectionClass
    {
        if (self::has($class) === false) {
            return null;
        }

        try {
            $cachedItem = unserialize(
                file_get_contents(self::formatToFileName($class)),
                [HydrateReflectionClass::class, HydrateReflectionProperty::class, HydrateReflectionNamedType::class]
            );

            if ($cachedItem instanceof HydrateReflectionClass) {
                return $cachedItem;
            }

            return null;
        } catch (Throwable $throwable) {
            return null;
        }
    }

    /** @param class-string $class */
    public static function set(string $class, HydrateReflectionClass $reflectionClass): void
    {
        file_put_contents(self::formatToFileName($class), serialize($reflectionClass));
    }

    /** @param class-string $class */
    public static function has(string $class): bool
    {
        return file_exists(self::formatToFileName($class));
    }

    /** @param class-string $class */
    public static function delete(string $class): void
    {
        unlink(self::formatToFileName($class));
    }

    public static function hasChanged(HydrateReflectionClass $reflection): bool
    {
        $classChangedAt = filemtime($reflection->getFileName());

        $cacheMadeAt = filemtime(self::formatToFileName($reflection->getName()));

        return $classChangedAt > $cacheMadeAt;
    }

    /** @param class-string $class */
    private static function formatToFileName(string $class): string
    {
        return sprintf('%s/%s%s', realpath(self::CACHE_FOLDER), base64_encode($class), '.json');
    }
}