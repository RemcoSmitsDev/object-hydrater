<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;
use RemcoSmits\Hydrate\Docblock\Type\CollectionType;
use RemcoSmits\Hydrate\Docblock\Type\IntType;
use RemcoSmits\Hydrate\Docblock\Type\ShapedCollection\ShapedCollectionItem;
use RemcoSmits\Hydrate\Docblock\Type\ShapedCollectionType;
use RemcoSmits\Hydrate\Docblock\Type\StringType;
use RemcoSmits\Hydrate\Docblock\Type\UnionType;
use Throwable;

final class TypeParser
{
    private static array $types = [];

    /**
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    public static function parse(string $type): AbstractType
    {
        $type = TypeParserUtil::removeUnnecessaryCharacters($type);

        $hasUnionType = strpos($type, '|') !== false;

        if ($hasUnionType && TypeParserUtil::mainTypeIsCollection($type) === false) {
            return new UnionType(self::splitToMultipleTypes($type));
        }

        if (preg_match('/\[\]|\<|\>|\{|\}/', $type) === 1) {
            return self::parseCollectionType($type);
        }

        if ($hasUnionType) {
            return new UnionType(self::splitToMultipleTypes($type));
        }

        if (preg_match('/^---child-collection-(\d+)---$/', $type) === 1) {
            return self::formatShapedArrayType();
        }

        return TypeParserUtil::mapStringToType($type);
    }

    /**
     * @throws FailedToParseDocblockToTypeException
     * @throws FailedToMapTypeException
     */
    private static function parseCollectionType(string $collectionType): AbstractType
    {
        // CollectionItem[] or mixed[] or string[]
        if (preg_match(TypeParserRegex::COLLECTION_TYPES_REGEX[0], $collectionType, $match) === 1) {
            return new CollectionType(
                'array',
                new UnionType([new IntType(), new StringType()]),
                self::parse($match['collectionItemTypeName'])
            );
        }

        // Collection<string> or array<string>
        if (preg_match(TypeParserRegex::COLLECTION_TYPES_REGEX[1], $collectionType, $match) === 1) {
            return new CollectionType(
                $match['collectionTypeName'],
                new UnionType([new IntType(), new StringType()]),
                self::parse($match['collectionItemTypeName'])
            );
        }

        // Collection<int, string> or array<int, string>
        if (preg_match(TypeParserRegex::COLLECTION_TYPES_REGEX[2], $collectionType, $match) === 1) {
            return new CollectionType(
                $match['collectionTypeName'],
                self::splitToUnionTypeIfIsUnion($match['collectionKeyTypes']),
                self::parse($match['collectionItemTypeName'])
            );
        }

        // array<int, array{remco: string|int, testing: string|int}> or array{remco: string|int, testing: string|int}
        try {
            self::$types = self::splitNestedCollectionTypes($collectionType);

            return self::formatShapedArrayType();
        } catch (Throwable $throwable) {
            throw new FailedToParseDocblockToTypeException(
                sprintf('Failed to parse [%s]', $collectionType)
            );
        }
    }

    /**
     * @return AbstractType[]
     *
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    private static function splitToMultipleTypes(string $typeString): array
    {
        if (preg_match('/\<|\{/', $typeString) === 0) {
            return array_map(
                static fn(string $type) => self::parse($type),
                explode('|', $typeString)
            );
        }

        $types = [];
        $currentType = '';
        $openings = 0;

        foreach (TypeParserRegex::matchAll($typeString) as $match) {
            if ($match === '<' || $match === '{') {
                ++$openings;
            }

            if ($match === '>' || $match === '}') {
                --$openings;
            }

            if ($match === '|' && $openings === 0) {
                $types[] = self::parse($currentType);
                $currentType = '';
                continue;
            }

            $currentType .= $match;
        }

        if (empty($currentType) === false) {
            $types[] = self::parse($currentType);
        }

        return $types;
    }

    /**
     * @return UnionType|AbstractType
     *
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    private static function splitToUnionTypeIfIsUnion(string $typeString): AbstractType
    {
        if (strpos($typeString, '|') === false) {
            return self::parse($typeString);
        }

        return new UnionType(self::splitToMultipleTypes($typeString));
    }

    /**
     * @return AbstractType
     *
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    private static function formatShapedArrayType(): AbstractType
    {
        $regex1 = '/([A-z0-9\.\-]+)(\?*)\:\s((?:([A-z\-\|]+)|---child-collection-(\d+)---)+)/';
        $regex2 = '/([A-z\|]+)\,\s((?:([A-z\-\|]+)|---child-collection-(\d+)---)+)/';

        $currentType = array_shift(self::$types);

        if (preg_match_all($regex1, $currentType, $match, PREG_UNMATCHED_AS_NULL) !== 0) {
            $collectionClass = new ShapedCollectionType(
                TypeParserUtil::getNameFromCollectionType($currentType),
            );

            foreach ($match[1] as $_key => $arrayKey) {
                $collectionClass->appendShape(
                    new ShapedCollectionItem(
                        $arrayKey,
                        $match[2][$_key] === '?',
                        self::parse($match[3][$_key])
                    )
                );
            }

            return $collectionClass;
        }

        if (preg_match($regex2, $currentType, $match, PREG_UNMATCHED_AS_NULL) !== 0) {
            $collectionClass = new CollectionType(
                TypeParserUtil::getNameFromCollectionType($currentType),
                self::splitToUnionTypeIfIsUnion($match[1])
            );

            $collectionClass->setSubType(self::parse($match[2]));

            return $collectionClass;
        }

        throw new FailedToParseDocblockToTypeException('failed to match something');
    }

    /**
     * @return array<int, string>
     *
     * @throws FailedToParseDocblockToTypeException
     */
    private static function splitNestedCollectionTypes(string $typeString): array
    {
        $types = [];
        $refKey = 0;
        $childArrKey = -1;

        foreach ($matches = TypeParserRegex::matchAll($typeString) as $part) {
            // when is collection
            $nextPart = next($matches);

            // when current is array
            // end next is < or {
            if ($nextPart === '<' || $nextPart === '{') {
                ++$childArrKey;

                if (array_key_exists($refKey, $types)) {
                    $types[$refKey] .= '---child-collection-' . $childArrKey . '---';
                }

                $refKey = count($types);

                if (isset($types[$refKey])) {
                    ++$refKey;
                }

                $types[] = $part;

                continue;
            }

            if ($part === '>' || $part === '}') {
                --$refKey;
                continue;
            }

            if (array_key_exists($refKey, $types)) {
                $types[$refKey] .= $part;
            } else {
                $types[] = $part;
            }
        }

        return $types;
    }
}
