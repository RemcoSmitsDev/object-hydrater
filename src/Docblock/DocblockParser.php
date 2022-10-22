<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock;

final class DocblockParser
{
    /** @return array<int, string> */
    private function explodeTypes(string $typeString): array
    {
        return preg_split('/(?<!\([A-z\<])\|(?![A-z ]*(\)|\>|\,|\}))/', $typeString);
    }

    public function parse(string $typeString): array
    {
        $types = [];

        foreach ($this->explodeTypes($typeString) as $type) {
            $types[] = TypeParser::parse($type);
        }

        return $types;
    }
}