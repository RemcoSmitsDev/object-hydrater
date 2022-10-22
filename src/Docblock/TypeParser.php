<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock;

use RemcoSmits\Hydrate\Docblock\Types\AbstractType;
use RemcoSmits\Hydrate\Docblock\Types\CollectionType;
use RemcoSmits\Hydrate\Docblock\Types\IntType;
use RemcoSmits\Hydrate\Docblock\Types\MixedType;
use RemcoSmits\Hydrate\Docblock\Types\NullType;
use RemcoSmits\Hydrate\Docblock\Types\ShapedCollection\ShapedCollectionItem;
use RemcoSmits\Hydrate\Docblock\Types\ShapedCollectionType;
use RemcoSmits\Hydrate\Docblock\Types\StringType;
use RemcoSmits\Hydrate\Docblock\Types\UnionType;
use RemcoSmits\Hydrate\Exception\FailedToParseDocblockToTypeException;
use RuntimeException;
use Throwable;

final class TypeParser
{
    public const TOKEN_REFERENCE = 0;
    public const TOKEN_UNION = 1;
    public const TOKEN_INTERSECTION = 2;
    public const TOKEN_NULLABLE = 3;
    public const TOKEN_OPEN_PARENTHESES = 4;
    public const TOKEN_CLOSE_PARENTHESES = 5;
    public const TOKEN_OPEN_ANGLE_BRACKET = 6;
    public const TOKEN_CLOSE_ANGLE_BRACKET = 7;
    public const TOKEN_OPEN_SQUARE_BRACKET = 8;
    public const TOKEN_CLOSE_SQUARE_BRACKET = 9;
    public const TOKEN_COMMA = 10;
    public const TOKEN_VARIADIC = 11;
    public const TOKEN_DOUBLE_COLON = 12;
    public const TOKEN_DOUBLE_ARROW = 13;
    public const TOKEN_EQUAL = 14;
    public const TOKEN_OPEN_PHPDOC = 15;
    public const TOKEN_CLOSE_PHPDOC = 16;
    public const TOKEN_PHPDOC_TAG = 17;
    public const TOKEN_FLOAT = 18;
    public const TOKEN_INTEGER = 19;
    public const TOKEN_SINGLE_QUOTED_STRING = 20;
    public const TOKEN_DOUBLE_QUOTED_STRING = 21;
    public const TOKEN_IDENTIFIER = 22;
    public const TOKEN_THIS_VARIABLE = 23;
    public const TOKEN_VARIABLE = 24;
    public const TOKEN_HORIZONTAL_WS = 25;
    public const TOKEN_PHPDOC_EOL = 26;
    public const TOKEN_OTHER = 27;
    public const TOKEN_END = 28;
    public const TOKEN_COLON = 29;
    public const TOKEN_WILDCARD = 30;
    public const TOKEN_OPEN_CURLY_BRACKET = 31;
    public const TOKEN_CLOSE_CURLY_BRACKET = 32;
    public const TOKEN_NEGATED = 33;
    public const TOKEN_ARROW = 34;

    public const TOKEN_LABELS = [
        self::TOKEN_REFERENCE => '\'&\'',
        self::TOKEN_UNION => '\'|\'',
        self::TOKEN_INTERSECTION => '\'&\'',
        self::TOKEN_NULLABLE => '\'?\'',
        self::TOKEN_NEGATED => '\'!\'',
        self::TOKEN_OPEN_PARENTHESES => '\'(\'',
        self::TOKEN_CLOSE_PARENTHESES => '\')\'',
        self::TOKEN_OPEN_ANGLE_BRACKET => '\'<\'',
        self::TOKEN_CLOSE_ANGLE_BRACKET => '\'>\'',
        self::TOKEN_OPEN_SQUARE_BRACKET => '\'[\'',
        self::TOKEN_CLOSE_SQUARE_BRACKET => '\']\'',
        self::TOKEN_OPEN_CURLY_BRACKET => '\'{\'',
        self::TOKEN_CLOSE_CURLY_BRACKET => '\'}\'',
        self::TOKEN_COMMA => '\',\'',
        self::TOKEN_COLON => '\':\'',
        self::TOKEN_VARIADIC => '\'...\'',
        self::TOKEN_DOUBLE_COLON => '\'::\'',
        self::TOKEN_DOUBLE_ARROW => '\'=>\'',
        self::TOKEN_ARROW => '\'->\'',
        self::TOKEN_EQUAL => '\'=\'',
        self::TOKEN_OPEN_PHPDOC => '\'/**\'',
        self::TOKEN_CLOSE_PHPDOC => '\'*/\'',
        self::TOKEN_PHPDOC_TAG => 'TOKEN_PHPDOC_TAG',
        self::TOKEN_PHPDOC_EOL => 'TOKEN_PHPDOC_EOL',
        self::TOKEN_FLOAT => 'TOKEN_FLOAT',
        self::TOKEN_INTEGER => 'TOKEN_INTEGER',
        self::TOKEN_SINGLE_QUOTED_STRING => 'TOKEN_SINGLE_QUOTED_STRING',
        self::TOKEN_DOUBLE_QUOTED_STRING => 'TOKEN_DOUBLE_QUOTED_STRING',
        self::TOKEN_IDENTIFIER => 'type',
        self::TOKEN_THIS_VARIABLE => '\'$this\'',
        self::TOKEN_VARIABLE => 'variable',
        self::TOKEN_HORIZONTAL_WS => 'TOKEN_HORIZONTAL_WS',
        self::TOKEN_OTHER => 'TOKEN_OTHER',
        self::TOKEN_END => 'TOKEN_END',
        self::TOKEN_WILDCARD => '*',
    ];

    public const COLLECTION_TYPES_REGEX = [
        '/^(?<collectionItemTypeName>[A-z]+)\[\]$/',
        '/^(?<collectionTypeName>[A-z]+)\<(?<collectionItemTypeName>[A-z]+)\>$/',
        '/^(?<collectionTypeName>[A-z]+)\<(?<collectionKeyTypes>[A-z\|]+),\s*(?<collectionItemTypeName>[A-z\|]+)\>$/'
    ];

    public const DEFAULT_ARRAY_KEY_TYPES = ['int', 'string'];

    /** @throws FailedToParseDocblockToTypeException */
    public static function parse(string $type): AbstractType
    {
        $type = self::removeUnnecessaryCharacters($type);

        if (preg_match('/\[\]|\<|\>|\{|\}/', $type) === 1) {
            return self::parseCollectionType($type);
        }

        if (strpos($type, '|') !== false) {
            return new UnionType(
                array_map(static fn(string $type) => self::parse($type), explode('|', $type))
            );
        }

        return self::mapStringToType($type);
    }

    private static function mapStringToType(string $type): AbstractType
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
            default:
                throw new RuntimeException(sprintf('failed to map type [%s]', $type));
        }
    }

    /** @throws FailedToParseDocblockToTypeException */
    private static function parseCollectionType(string $collectionType): AbstractType
    {
        // CollectionItem[] or mixed[] or string[]
        if (preg_match(self::COLLECTION_TYPES_REGEX[0], $collectionType, $match) === 1) {
            return new CollectionType(
                'array',
                self::DEFAULT_ARRAY_KEY_TYPES,
                self::splitToMultipleTypes($match['collectionItemTypeName'])
            );
        }

        // Collection<string> or array<string>
        if (preg_match(self::COLLECTION_TYPES_REGEX[1], $collectionType, $match) === 1) {
            return new CollectionType(
                $match['collectionTypeName'],
                self::DEFAULT_ARRAY_KEY_TYPES,
                self::splitToMultipleTypes($match['collectionItemTypeName'])
            );
        }

        // Collection<int, string> or array<int, string>
        if (preg_match(self::COLLECTION_TYPES_REGEX[2], $collectionType, $match) === 1) {
            return new CollectionType(
                $match['collectionTypeName'],
                self::splitToMultipleTypes($match['collectionKeyTypes']),
                self::splitToMultipleTypes($match['collectionItemTypeName'])
            );
        }

        // array<int, array{remco: string|int, testing: string|int}> or array{remco: string|int, testing: string|int}
        try {
            $types = self::splitNestedCollectionTypes($collectionType);

            return self::formatShapedArrayType($types);
        } catch (Throwable $throwable) {
            dd($throwable);
            throw new FailedToParseDocblockToTypeException(
                sprintf('Failed to parse [%s]', $collectionType)
            );
        }
    }

    private static function splitToMultipleTypes(string $typeString): array
    {
        return array_map(static fn(string $type) => self::mapStringToType($type), explode('|', $typeString));
    }

    private static function removeUnnecessaryCharacters(string $typeString): string
    {
        return str_replace(['(', ')', '&', '\'', '"'], '', $typeString);
    }

    private static function generateRegexp(): string
    {
        $patterns = [
            self::TOKEN_HORIZONTAL_WS => '[\\x09\\x20]++',

            self::TOKEN_IDENTIFIER => '(?:[\\\\]?+[a-z_\\x80-\\xFF][0-9a-z_\\x80-\\xFF-]*+)++',
            self::TOKEN_THIS_VARIABLE => '\\$this(?![0-9a-z_\\x80-\\xFF])',
            self::TOKEN_VARIABLE => '\\$[a-z_\\x80-\\xFF][0-9a-z_\\x80-\\xFF]*+',

            // '&' followed by TOKEN_VARIADIC, TOKEN_VARIABLE, TOKEN_EQUAL, TOKEN_EQUAL or TOKEN_CLOSE_PARENTHESES
            self::TOKEN_REFERENCE => '&(?=\\s*+(?:[.,=)]|(?:\\$(?!this(?![0-9a-z_\\x80-\\xFF])))))',
            self::TOKEN_UNION => '\\|',
            self::TOKEN_INTERSECTION => '&',
            self::TOKEN_NULLABLE => '\\?',
            self::TOKEN_NEGATED => '!',

            self::TOKEN_OPEN_PARENTHESES => '\\(',
            self::TOKEN_CLOSE_PARENTHESES => '\\)',
            self::TOKEN_OPEN_ANGLE_BRACKET => '<',
            self::TOKEN_CLOSE_ANGLE_BRACKET => '>',
            self::TOKEN_OPEN_SQUARE_BRACKET => '\\[',
            self::TOKEN_CLOSE_SQUARE_BRACKET => '\\]',
            self::TOKEN_OPEN_CURLY_BRACKET => '\\{',
            self::TOKEN_CLOSE_CURLY_BRACKET => '\\}',

            self::TOKEN_COMMA => ',',
            self::TOKEN_VARIADIC => '\\.\\.\\.',
            self::TOKEN_DOUBLE_COLON => '::',
            self::TOKEN_DOUBLE_ARROW => '=>',
            self::TOKEN_ARROW => '->',
            self::TOKEN_EQUAL => '=',
            self::TOKEN_COLON => ':',

            self::TOKEN_OPEN_PHPDOC => '/\\*\\*(?=\\s)\\x20?+',
            self::TOKEN_CLOSE_PHPDOC => '\\*/',
            self::TOKEN_PHPDOC_TAG => '@[a-z][a-z0-9-\\\\]*+',
            self::TOKEN_PHPDOC_EOL => '\\r?+\\n[\\x09\\x20]*+(?:\\*(?!/)\\x20?+)?',

            self::TOKEN_FLOAT => '(?:-?[0-9]++\\.[0-9]*+(?:e-?[0-9]++)?)|(?:-?[0-9]*+\\.[0-9]++(?:e-?[0-9]++)?)|(?:-?[0-9]++e-?[0-9]++)',
            self::TOKEN_INTEGER => '-?(?:(?:0b[0-1]++)|(?:0o[0-7]++)|(?:0x[0-9a-f]++)|(?:[0-9]++))',
            self::TOKEN_SINGLE_QUOTED_STRING => '\'(?:\\\\[^\\r\\n]|[^\'\\r\\n\\\\])*+\'',
            self::TOKEN_DOUBLE_QUOTED_STRING => '"(?:\\\\[^\\r\\n]|[^"\\r\\n\\\\])*+"',

            self::TOKEN_WILDCARD => '\\*',

            // anything but TOKEN_CLOSE_PHPDOC or TOKEN_HORIZONTAL_WS or TOKEN_EOL
            self::TOKEN_OTHER => '(?:(?!\\*/)[^\\s])++',
        ];

        foreach ($patterns as &$pattern) {
            $pattern = '(?:' . $pattern . ')';
        }

        return '~' . implode('|', $patterns) . '~Asi';
    }


    /**
     * @throws FailedToParseDocblockToTypeException
     */
    private static function formatShapedArrayType(array &$types): AbstractType
    {
        $r = '/([A-z0-9\.\-_]+)(\?*)\:\s(?:([A-z\|]+)|---child-collection-([0-9]+)---)/';
        $r2 = '/([A-z\|]+)\,\s(?:([A-z\|]+)|---child-collection-([0-9]+)---)/';

        // array1<int, array2{1: array3{2: array{remco: string, smits: string}}, a: string, geen: array{aarde: string}}>
        $currentType = array_shift($types);

        if (preg_match_all($r, $currentType, $match, PREG_UNMATCHED_AS_NULL) !== 0) {
            $collectionClass = new ShapedCollectionType(
                preg_replace('/^([A-z0-9]+)\{.*/', '$1', $currentType),
            );

            foreach ($match[1] as $_key => $arrayKey) {
                $isOptional = $match[2][$_key] === '?';

                if (is_numeric($match[4][$_key])) {
                    $collectionClass->appendShape(
                        new ShapedCollectionItem(
                            $arrayKey,
                            $isOptional,
                            self::formatShapedArrayType($types)
                        )
                    );
                } else {
                    $collectionClass->appendShape(
                        new ShapedCollectionItem($arrayKey, $isOptional, self::parse($match[3][$_key]))
                    );
                }
            }

            return $collectionClass;
        }

        if (preg_match($r2, $currentType, $match, PREG_UNMATCHED_AS_NULL) !== 0) {
            $collectionClass = new CollectionType(
                preg_replace('/^([A-z0-9]+)\<.*/', '$1', $currentType),
                ['int'],
                []
            );

            $collectionClass->setSubType(
                is_numeric($match[3]) ? self::formatShapedArrayType($types) : self::parse($match[1] ?? 'failed')
            );

            return $collectionClass;
        }

        throw new FailedToParseDocblockToTypeException('failed to match something');
    }

    private static function splitNestedCollectionTypes(string $typeString): array
    {
        $types = [];
        $refKey = 0;

        $childArrKey = -1;

        preg_match_all(self::generateRegexp(), $typeString, $matches);

        $matches = $matches[0];

        foreach ($matches as $part) {
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

            $types[$refKey] .= $part;
        }

        return $types;
    }
}
