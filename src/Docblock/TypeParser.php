<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Parser\ClassStringParser;
use RemcoSmits\Hydrate\Docblock\Parser\CollectionParser;
use RemcoSmits\Hydrate\Docblock\Parser\ShapedCollectionParser;
use RemcoSmits\Hydrate\Docblock\Parser\UnionTypeParser;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;

final class TypeParser
{
    /**
     * @throws FailedToParseDocblockToTypeException
     * @throws FailedToMapTypeException
     */
    private static function mapParsers(string $typeString): AbstractType
    {
        try {
            return TypeParserUtil::mapStringToType($typeString);
        } catch (FailedToMapTypeException $exception) {
        }

        $parsers = [
            new UnionTypeParser(),
            new ClassStringParser(),
            new CollectionParser(),
            new ShapedCollectionParser()
        ];

        foreach ($parsers as $parser) {
            try {
                if (preg_match($parser->matchFormat(), $typeString, $matches) === 1) {
                    return $parser->parse($typeString, $matches);
                }
            } catch (InvalidTypeFormatException $exception) {
            }
        }

        throw new FailedToMapTypeException($typeString);
    }

    /**
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    public static function parse(string $type): AbstractType
    {
        if (preg_match('/^---child-collection-(\d+)---$/', $type) === 1) {
            dd($type);
        }

        return self::mapParsers(
            TypeParserUtil::removeUnnecessaryCharacters($type)
        );

        if (preg_match('/^---child-collection-(\d+)---$/', $type) === 1) {
            return self::formatShapedArrayType();
        }

        return TypeParserUtil::mapStringToType($type);
    }
}
