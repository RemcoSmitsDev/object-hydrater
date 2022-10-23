<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use Throwable;

// COVERT:
// @var Type[]
// @var array<Type>
// @var array<string, Type>
// @var array<int, Type>
// @var Collection<SubClass>
final class DocblockParser
{
    private const FULL_TYPE_REGEX = '@var\s+([A-z]+)';

    private const COLLECTION_TYPES_REGEX = [
        '@var\s+([A-z]+)\[\]',
        '@var\s+([A-z]+)\<([A-z]+)\>',
        '@var\s+([A-z]+)\<[A-z]+,\s+([A-z]+)\>',
    ];

    /** @throws \RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException */
    public static function getType(string $docblock): PropertyType
    {
        try {
            return self::getArrayType($docblock);
        } catch (Throwable $throwable) {
            $info = self::parseDocblock(self::FULL_TYPE_REGEX, $docblock);

            return new PropertyType($info[0], $info[0]);
        }
    }

    /** @throws FailedToParseDocblockToTypeException */
    private static function getArrayType(string $docblock): PropertyType
    {
        $res = self::parseDocblock(implode('|', self::COLLECTION_TYPES_REGEX), $docblock);

        // array<CollectionItemType>
        // CollectionItemType[]
        // Collection<CollectionItemType>
        $collectionItemType = $res[1] ?? null;

        // when @var Type[] is used
        if (count($res) === 1) {
            $collectionItemType = $res[0];
            $res[0] = 'array';
        }

        return new PropertyType($res[0], $res[0], $collectionItemType);
    }

    /**
     * @return array<int, string>
     *
     * @throws \RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException
     */
    private static function parseDocblock(string $regex, string $docblock): array
    {
        preg_match(sprintf('/%s/', $regex), $docblock, $match);

        if (empty($match)) {
            throw new FailedToParseDocblockToTypeException(
                sprintf('failed to parse [%s]', $docblock)
            );
        }

        $match = array_filter($match);

        array_shift($match);

        return $match;
    }
}