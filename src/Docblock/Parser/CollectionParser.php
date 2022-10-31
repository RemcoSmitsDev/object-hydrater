<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;
use RemcoSmits\Hydrate\Docblock\Type\CollectionType;
use RemcoSmits\Hydrate\Docblock\Type\IntType;
use RemcoSmits\Hydrate\Docblock\Type\StringType;
use RemcoSmits\Hydrate\Docblock\Type\UnionType;
use RemcoSmits\Hydrate\Docblock\TypeParser;
use RemcoSmits\Hydrate\Docblock\TypeParserRegex;

final class CollectionParser extends AbstractParser
{
    private const COLLECTION_TYPES_REGEX = [
        '(?<name1>[A-z]+)\[\]',
        '(?<name2>[A-z\-]+)\<(?<itemName2>.+)\>',
    ];

    /** @inheritDoc */
    public function matchFormat(): string
    {
        return sprintf('/^(?:%s)$/', implode('|', self::COLLECTION_TYPES_REGEX));
    }

    /**
     * @param array<string|int, string|null> $matches
     *
     * @throws InvalidTypeFormatException
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    public function parse(string $typeString, array $matches): AbstractType
    {
        if (empty($matches)) {
            throw new InvalidTypeFormatException();
        }

        if (empty($matches['name1']) === false) {
            return new CollectionType(
                'array',
                new UnionType([new IntType(), new StringType()]),
                TypeParser::parse($matches['name1'])
            );
        }

        [$keys, $items] = $this->splitKeysFromItems(
            preg_replace('/^[A-z]+\<(.+)\>$/', '$1', $typeString)
        );

        if (empty($matches['name2']) === false && empty($keys) && empty($items) === false) {
            return new CollectionType(
                'array',
                new UnionType([new IntType(), new StringType()]),
                TypeParser::parse(trim($items))
            );
        }

        if (empty($matches['name2']) === false && empty($keys) === false && empty($items) === false) {
            return new CollectionType(
                'array',
                TypeParser::parse(trim($keys)),
                TypeParser::parse(trim($items))
            );
        }

        throw new InvalidTypeFormatException();
    }

    /**
     * @return array{0: string, 1: string}
     *
     * @throws FailedToParseDocblockToTypeException
     */
    private function splitKeysFromItems(string $typeString): array
    {
        $currentType = '';
        $keys = '';
        $openings = 0;

        if (strpos($typeString, ',') === false) {
            return [
                '',
                $typeString
            ];
        }

        foreach (TypeParserRegex::matchAll($typeString) as $match) {
            if ($match === '<' || $match === '{') {
                ++$openings;
            }

            if ($match === '>' || $match === '}') {
                --$openings;
            }

            if ($match === ',' && $openings === 0) {
                $keys = $currentType;
                $currentType = '';
                continue;
            }

            $currentType .= $match;
        }

        return [
            $keys,
            $currentType
        ];
    }
}