<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Parser;

use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\InvalidTypeFormatException;
use RemcoSmits\Hydrate\Docblock\Type\AbstractType;
use RemcoSmits\Hydrate\Docblock\Type\CollectionType;
use RemcoSmits\Hydrate\Docblock\Type\ShapedCollection\ShapedCollectionItem;
use RemcoSmits\Hydrate\Docblock\Type\ShapedCollectionType;
use RemcoSmits\Hydrate\Docblock\TypeParser;
use RemcoSmits\Hydrate\Docblock\TypeParserRegex;
use RemcoSmits\Hydrate\Docblock\TypeParserUtil;

final class ShapedCollectionParser extends AbstractParser
{
    private array $types;


    /** @inheritDoc */
    public function matchFormat(): string
    {
        return '/^([A-z0-9]+)\{.+\}$/';
    }

    /**
     * @throws FailedToParseDocblockToTypeException
     * @throws InvalidTypeFormatException
     * @throws FailedToMapTypeException
     */
    public function parse(string $typeString, array $matches): AbstractType
    {
        if (empty($matches)) {
            throw new InvalidTypeFormatException();
        }

        $this->types = $this->splitNestedCollectionTypes($typeString);

        dd($this->types);

        return $this->formatShapedArrayType();
    }

    /**
     * @throws FailedToParseDocblockToTypeException
     * @throws FailedToMapTypeException
     */
    private function doThing(string $typeString): AbstractType
    {
        if (preg_match('/^---child-collection-(\d+)---$/', $typeString) === 1) {
            return $this->formatShapedArrayType();
        }

        return TypeParser::parse($typeString);
    }

    /**
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    private function formatShapedArrayType(): AbstractType
    {
        $regex1 = '/([A-z0-9\.\-]+)(\?*)\:\s((?:([A-z\-\|]+)|---child-collection-(\d+)---)+)/';
        $regex2 = '/([A-z\|]+)\,\s((?:([A-z\-\|]+)|---child-collection-(\d+)---)+)/';

        $currentType = array_shift($this->types);

        if (preg_match_all($regex1, $currentType, $match, PREG_UNMATCHED_AS_NULL) !== 0) {
            $collectionClass = new ShapedCollectionType(
                TypeParserUtil::getNameFromCollectionType($currentType),
            );

            foreach ($match[1] as $_key => $arrayKey) {
                $collectionClass->appendShape(
                    new ShapedCollectionItem(
                        $arrayKey,
                        $match[2][$_key] === '?',
                        $this->doThing($match[3][$_key])
                    )
                );
            }

            return $collectionClass;
        }

        if (preg_match($regex2, $currentType, $match, PREG_UNMATCHED_AS_NULL) !== 0) {
            $collectionClass = new CollectionType(
                TypeParserUtil::getNameFromCollectionType($currentType),
                $this->doThing($match[1])
            );

            $collectionClass->setSubType($this->doThing($match[2]));

            return $collectionClass;
        }

        throw new FailedToParseDocblockToTypeException('failed to match something');
    }

    /**
     * @return array<int, string>
     *
     * @throws FailedToParseDocblockToTypeException
     */
    private function splitNestedCollectionTypes(string $typeString): array
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