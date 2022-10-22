<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Docblock\Types\ShapedCollection;

use RemcoSmits\Hydrate\Docblock\Types\AbstractType;

final class ShapedCollectionItem
{
    /** @var string|int */
    private $key;

    private bool $isOptional;

    private AbstractType $type;

    /**
     * @param string|int $key
     */
    public function __construct($key, bool $isOptional, AbstractType $type)
    {
        $this->key = $key;
        $this->isOptional = $isOptional;
        $this->type = $type;
    }

    /** @return int|string */
    public function getKey()
    {
        return $this->key;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function getType(): AbstractType
    {
        return $this->type;
    }
}

//array<int,
//    array1{
//        remco: string|int,
//        normal: array2{
//                    t: string,
//                    a: array{
//                        h: string,
//                        t: mixed
//                    }
//        },
//        a: string,
//        1: mixed,
//        array: array3{
//                age: int,
//                name: string,
//                hobbies: array4{
//                            0: string,
//                            1?: string|int
//                }
//        }
//    }
//>