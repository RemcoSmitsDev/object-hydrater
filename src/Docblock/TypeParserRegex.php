<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;

final class TypeParserRegex
{
    public const COLLECTION_TYPES_REGEX = [
        '/^(?<collectionItemTypeName>[A-z]+)\[\]$/',
        '/^(?<collectionTypeName>[A-z]+)\<(?<collectionItemTypeName>[A-z]+)\>$/',
        '/^(?<collectionTypeName>[A-z]+)\<(?<collectionKeyTypes>[A-z\|]+),\s*(?<collectionItemTypeName>[A-z\|]+)\>$/'
    ];

    public const TYPE_REGEX = [
        '[\\x09\\x20]++',
        '(?:[\\\\]?+[a-z_\\x80-\\xFF][0-9a-z_\\x80-\\xFF-]*+)++',
        '\\$this(?![0-9a-z_\\x80-\\xFF])',
        '\\$[a-z_\\x80-\\xFF][0-9a-z_\\x80-\\xFF]*+',
        '&(?=\\s*+(?:[.,=)]|(?:\\$(?!this(?![0-9a-z_\\x80-\\xFF])))))',
        '\\|',
        '&',
        '\\?',
        '!',
        '\\(',
        '\\)',
        '<',
        '>',
        '\\[',
        '\\]',
        '\\{',
        '\\}',
        ',',
        '\\.\\.\\.',
        '::',
        '=>',
        '->',
        '=',
        ':',
        '/\\*\\*(?=\\s)\\x20?+',
        '\\*/',
        '@[a-z][a-z0-9-\\\\]*+',
        '\\r?+\\n[\\x09\\x20]*+(?:\\*(?!/)\\x20?+)?',
        '(?:-?[0-9]++\\.[0-9]*+(?:e-?[0-9]++)?)|(?:-?[0-9]*+\\.[0-9]++(?:e-?[0-9]++)?)|(?:-?[0-9]++e-?[0-9]++)',
        '-?(?:(?:0b[0-1]++)|(?:0o[0-7]++)|(?:0x[0-9a-f]++)|(?:[0-9]++))',
        '\'(?:\\\\[^\\r\\n]|[^\'\\r\\n\\\\])*+\'',
        '"(?:\\\\[^\\r\\n]|[^"\\r\\n\\\\])*+"',
        '\\*',
        '(?:(?!\\*/)[^\\s])++',
    ];

    public static function generateRegexp(): string
    {
        return sprintf(
            '~%s~Asi',
            implode(
                '|',
                array_map(static fn(string $pattern) => sprintf('(?:%s)', $pattern), self::TYPE_REGEX)
            )
        );
    }

    /** @throws \RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException */
    public static function matchAll(string $typeString): array
    {
        preg_match_all(self::generateRegexp(), $typeString, $matches);

        if (isset($matches[0]) === false || is_array($matches[0]) === false) {
            throw new FailedToParseDocblockToTypeException($typeString);
        }

        return $matches[0];
    }
}