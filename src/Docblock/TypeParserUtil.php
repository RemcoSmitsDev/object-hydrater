<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Types\AbstractType;
use RemcoSmits\Hydrate\Docblock\Types\IntType;
use RemcoSmits\Hydrate\Docblock\Types\MixedType;
use RemcoSmits\Hydrate\Docblock\Types\NullType;
use RemcoSmits\Hydrate\Docblock\Types\ScalarType;
use RemcoSmits\Hydrate\Docblock\Types\StringType;

final class TypeParserUtil
{
    public static function removeUnnecessaryCharacters(string $typeString): string
    {
        return str_replace(
            ['(', ')', '\'', '"'],
            '',
            trim(
                self::changeIntersectTypeToUnion($typeString)
            )
        );
    }

    public static function changeIntersectTypeToUnion(string $typeString): string
    {
        return str_replace('&', '|', $typeString);
    }

    public static function mainTypeIsCollection(string $type): bool
    {
        return preg_match('/^[A-z0-9]+(\<|\{)/', $type) !== 0;
    }

    public static function getNameFromCollectionType(string $typeString): string
    {
        return preg_replace('/^([A-z0-9]+)([{<]).*/', '$1', $typeString);
    }

    /** @throws FailedToMapTypeException */
    public static function mapStringToType(string $type): AbstractType
    {
        switch ($type) {
            case 'string':
                return new StringType();
            case 'null':
                return new NullType();
            case 'int':
            case 'integer':
                return new IntType();
            case 'mixed':
                return new MixedType();
            case 'scalar':
                return new ScalarType();
            default:
                throw new FailedToMapTypeException($type);
        }
    }
}