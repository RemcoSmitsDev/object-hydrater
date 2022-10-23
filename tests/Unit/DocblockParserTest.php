<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemcoSmits\Hydrate\Docblock\TypeParser;
use RemcoSmits\Hydrate\Docblock\Types\CollectionType;
use RemcoSmits\Hydrate\Docblock\Types\IntType;
use RemcoSmits\Hydrate\Docblock\Types\MixedType;
use RemcoSmits\Hydrate\Docblock\Types\NullType;
use RemcoSmits\Hydrate\Docblock\Types\ShapedCollection\ShapedCollectionItem;
use RemcoSmits\Hydrate\Docblock\Types\ShapedCollectionType;
use RemcoSmits\Hydrate\Docblock\Types\StringType;
use RemcoSmits\Hydrate\Docblock\Types\UnionType;
use RemcoSmits\Hydrate\Exception\FailedToParseDocblockToTypeException;

class DocblockParserTest extends TestCase
{
    /** @throws FailedToParseDocblockToTypeException */
    public function testItCanParseDocblockToType(): void
    {
        $typeString = 'array<int, array1{remco: string|int, normal: array2{t: string, a: array{h: string, t: mixed}}, a: string, 1: mixed, array: array3{age: int, name: string, hobbies: array4{0: string, 1?: string|int}}}>';

        $response = TypeParser::parse($typeString);

        $type = new CollectionType(
            'array',
            [new IntType()],
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

        $this->assertEquals($response, $type);
    }

    /**
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
                [new IntType(), new StringType()],
                new MixedType()
            ),
            new CollectionType(
                'Collection',
                [new IntType(), new StringType()],
                new StringType()
            ),
            new CollectionType(
                'array',
                [new IntType(), new StringType()],
                new StringType()
            ),
            new CollectionType(
                'array',
                [new IntType()],
                new StringType()
            ),
            new CollectionType(
                'array',
                [new IntType()],
                new UnionType(
                    [new StringType(), new IntType()]
                )
            ),
            new CollectionType(
                'array',
                [new IntType()],
                new ShapedCollectionType(
                    'array',
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
                            'testing',
                            false,
                            new UnionType([
                                new StringType(),
                                new IntType()
                            ])
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

        $this->assertEquals($response, $type);
    }
}