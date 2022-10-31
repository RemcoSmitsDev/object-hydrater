<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;
use RemcoSmits\Hydrate\Docblock\Type\UnionType;
use RemcoSmits\Hydrate\Docblock\TypeParser;
use RemcoSmits\Hydrate\Docblock\TypeParserRegex;
use RemcoSmits\Hydrate\Docblock\TypeParserUtil;

final class UnionTypeParser extends AbstractParser
{

    /** @inheritDoc */
    public function matchFormat(): string
    {
        return '/^.+\|.+$/';
    }

    /**
     * @param array<string|int, string|null> $matches
     *
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     * @throws InvalidTypeFormatException
     */
    public function parse(string $typeString, array $matches): AbstractType
    {
        if (TypeParserUtil::mainTypeIsCollection($typeString)) {
            throw new InvalidTypeFormatException();
        }

        return new UnionType($this->splitToMultipleTypes($typeString));
    }


    /**
     * @throws FailedToParseDocblockToTypeException
     * @throws InvalidTypeFormatException
     * @throws FailedToMapTypeException
     */
    private function splitToMultipleTypes(string $typeString): array
    {
        if (preg_match('/\<|\{/', $typeString) === 0) {
            return array_map(
                static fn(string $type) => TypeParser::parse($type),
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
                $types[] = TypeParser::parse($currentType);
                $currentType = '';
                continue;
            }

            $currentType .= $match;
        }

        if (empty($currentType) === false) {
            $types[] = TypeParser::parse($currentType);
        }

        if ($openings !== 0) {
            throw new InvalidTypeFormatException();
        }

        return $types;
    }
}