<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToMapTypeException;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Docblock\Type\CollectionType;
use RemcoSmits\Hydrate\Docblock\Type\IntType;
use RemcoSmits\Hydrate\Docblock\Type\MixedType;
use RemcoSmits\Hydrate\Docblock\Type\NullType;
use RemcoSmits\Hydrate\Docblock\Type\ShapedCollection\ShapedCollectionItem;
use RemcoSmits\Hydrate\Docblock\Type\ShapedCollectionType;
use RemcoSmits\Hydrate\Docblock\Type\StringType;
use RemcoSmits\Hydrate\Docblock\Type\UnionType;
use RemcoSmits\Hydrate\Docblock\TypeParser;

class DocblockParserTest extends TestCase
{
    /**
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    public function testItCanParseDocblockToType(): void
    {
        $typeString = 'array<int, array1{remco: string|int, normal: array2{t: string, a: array{h: string, t: mixed}}, a: string, 1: mixed, array: array3{age: int, name: string, hobbies: array4{0: string, 1?: string|int}}}>';

        $response = TypeParser::parse($typeString);

        $type = new CollectionType(
            'array',
            new IntType(),
            new ShapedCollectionType(
                'array1',
                [
                    new ShapedCollectionItem(
                        'remco',
                        false,
                        new UnionType([
                            new StringType(),
                            new IntType()
                        ])
                    ),
                    new ShapedCollectionItem(
                        'normal',
                        false,
                        new ShapedCollectionType(
                            'array2',
                            [
                                new ShapedCollectionItem(
                                    't',
                                    false,
                                    new StringType()
                                ),
                                new ShapedCollectionItem(
                                    'a',
                                    false,
                                    new ShapedCollectionType(
                                        'array',
                                        [
                                            new ShapedCollectionItem(
                                                'h',
                                                false,
                                                new StringType()
                                            ),
                                            new ShapedCollectionItem(
                                                't',
                                                false,
                                                new MixedType()
                                            )
                                        ]
                                    )
                                )
                            ]
                        )
                    ),
                    new ShapedCollectionItem(
                        'a',
                        false,
                        new StringType()
                    ),
                    new ShapedCollectionItem(
                        '1',
                        false,
                        new MixedType()
                    ),
                    new ShapedCollectionItem(
                        'array',
                        false,
                        new ShapedCollectionType(
                            'array3',
                            [
                                new ShapedCollectionItem(
                                    'age',
                                    false,
                                    new IntType()
                                ),
                                new ShapedCollectionItem(
                                    'name',
                                    false,
                                    new StringType()
                                ),
                                new ShapedCollectionItem(
                                    'hobbies',
                                    false,
                                    new ShapedCollectionType(
                                        'array4',
                                        [
                                            new ShapedCollectionItem(
                                                '0',
                                                false,
                                                new StringType()
                                            ),
                                            new ShapedCollectionItem(
                                                '1',
                                                true,
                                                new UnionType(
                                                    [
                                                        new StringType(),
                                                        new IntType()
                                                    ]
                                                )
                                            )
                                        ]
                                    )
                                )
                            ]
                        )
                    )
                ]
            )
        );

        $this->assertEquals($type, $response);
    }

    /**
     * @throws FailedToMapTypeException
     * @throws FailedToParseDocblockToTypeException
     */
    public function testItCanParseUnionWithArray(): void
    {
        $typeString = 'string|null|mixed[]|Collection<string>|array<string>|array<int, string>|array<int, string|int>|array<int, array{remco: string|int, testing: string|int, a: string, 1: mixed, array: array{age: int, name: string, hobbies: array{0: string, 1: string}}}>';

        $response = TypeParser::parse($typeString);

        $type = new UnionType([
            new StringType(),
            new NullType(),
            new CollectionType(
                'array',
                new UnionType([new IntType(), new StringType()]),
                new MixedType()
            ),
            new CollectionType(
                'Collection',
                new UnionType([new IntType(), new StringType()]),
                new StringType()
            ),
            new CollectionType(
                'array',
                new UnionType([new IntType(), new StringType()]),
                new StringType()
            ),
            new CollectionType(
                'array',
                new IntType(),
                new StringType()
            ),
            new CollectionType(
                'array',
                new IntType(),
                new UnionType([new StringType(), new IntType()])
            ),
            new CollectionType(
                'array',
                new IntType(),
                new ShapedCollectionType(
                    'array',
                    [
                        new ShapedCollectionItem(
                            'remco',
                            false,
                            new UnionType([new StringType(), new IntType()])
                        ),
                        new ShapedCollectionItem(
                            'testing',
                            false,
                            new UnionType([new StringType(), new IntType()])
                        ),
                        new ShapedCollectionItem(
                            'a',
                            false,
                            new StringType()
                        ),
                        new ShapedCollectionItem(
                            '1',
                            false,
                            new MixedType(),
                        ),
                        new ShapedCollectionItem(
                            'array',
                            false,
                            new ShapedCollectionType(
                                'array',
                                [
                                    new ShapedCollectionItem(
                                        'age',
                                        false,
                                        new IntType()
                                    ),
                                    new ShapedCollectionItem(
                                        'name',
                                        false,
                                        new StringType(),
                                    ),
                                    new ShapedCollectionItem(
                                        'hobbies',
                                        false,
                                        new ShapedCollectionType(
                                            'array',
                                            [
                                                new ShapedCollectionItem(
                                                    '0',
                                                    false,
                                                    new StringType(),
                                                ),
                                                new ShapedCollectionItem(
                                                    '1',
                                                    false,
                                                    new StringType(),
                                                )
                                            ]
                                        )
                                    )
                                ]
                            )
                        )
                    ]
                )
            )
        ]);

        $this->assertEquals($type, $response);
    }

    /**
     * @throws FailedToParseDocblockToTypeException
     * @throws FailedToMapTypeException
     */
    public function testItCanDoStuff(): void
    {
        $response = TypeParser::parse('array<string, string|mixed|array{remco: string, smits: string}|int>');

        $type = new CollectionType(
            'array',
            new StringType(),
            new UnionType([
                new StringType(),
                new MixedType(),
                new ShapedCollectionType(
                    'array',
                    [
                        new ShapedCollectionItem('remco', false, new StringType()),
                        new ShapedCollectionItem('smits', false, new StringType()),
                    ]
                ),
                new IntType()
            ])
        );

        $this->assertEquals($type, $response);
    }

    public function testClassString(): void
    {
//        dd('failed');
        $response = TypeParser::parse('array<string, string|class-string>');

        dd('response: ', $response);
    }

//    /**
//     * @throws FailedToParseDocblockToTypeException
//     */
//    public function testSplitShapedCollection(): void
//    {
//        $t = 'array<int, array1{remco: string|int, normal: array2{t: string, a: array<string>}, a: string, 1: mixed, array: array3{age: int, name: string, hobbies: array4{0: string, 1?: string|int}}}>';
//
//        $array = [
//            'array<int, ---child-collection-1--->',
//            'array1{remco: string|int, normal: ---child-collection-2---, a: string, 1: mixed, array: ---child-collection-4---}',
//            'array2{t: string, a: ---child-collection-3---}',
//            'array<string>',
//            'array3{age: int, name: string, hobbies: ---child-collection-5---}',
//            'array4{0: string, 1?: string|int}'
//        ];
//
//        $types = [];
//        $index = 0;
//        $indexes = [];
//
//        foreach ($matches = TypeParserRegex::matchAll($t) as $part) {
//            $nextPart = next($matches);
//
//            if ($nextPart === '<' || $nextPart === '{') {
//                $types[] = $part;
//
//                ++$index;
//            }
//
//            if ($part === '<' || $part === '{') {
//                $indexes[] = $index;
//                $types[$index - 1] .= $part;
//            }
//
//            if ($nextPart === '>' || $nextPart === '}') {
////                dump($types, $index, $indexes);
//            }
//
//            if ($part === '>' || $part === '}') {
//                --$index;
//
//                $types[$index] .= $part;
//            }
//        }
//
//        dd($types, $indexes);
//
//        $this->assertEquals(0, $index);
//
//        $this->assertEquals($array, $types);
//    }
}