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

        $this->assertInstanceOf(CollectionType::class, $response);
        assert($response instanceof CollectionType);

        $this->assertEquals('array', $response->getTypeName());
        $this->assertInstanceOf(ShapedCollectionType::class, $response->getSubType());

        $subType = $response->getSubType();

        assert($subType instanceof ShapedCollectionType);
        $this->assertEquals('array1', $subType->getTypeName());
        $this->assertIsArray($subType->getShapes());
        $this->assertNotEmpty($subType->getShapes());
        $this->assertCount(5, $subType->getShapes());


        // FIRST SHAPE
        $shape = $subType->getShapes()[0];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('remco', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(UnionType::class, $shape->getType());

        $unionType = $shape->getType();
        assert($unionType instanceof UnionType);

        $this->assertIsArray($unionType->getTypes());
        $this->assertCount(2, $unionType->getTypes());
        $this->assertInstanceOf(StringType::class, $unionType->getTypes()[0]);
        $this->assertInstanceOf(IntType::class, $unionType->getTypes()[1]);


        // SECOND SHAPE
        $shape = $subType->getShapes()[1];

        $this->assertEquals('normal', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(ShapedCollectionType::class, $shape->getType());
        $this->assertEquals('array2', $shape->getType()->getTypeName());

        // SECOND SHAPE NESTED 1
        $subType = $shape->getType();

        assert($subType instanceof ShapedCollectionType);

        $this->assertIsArray($subType->getShapes());
        $this->assertCount(2, $subType->getShapes());

        $shape = $subType->getShapes()[0];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('t', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(StringType::class, $shape->getType());

        $shape = $subType->getShapes()[1];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('a', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(ShapedCollectionType::class, $shape->getType());

        $subType = $shape->getType();

        assert($subType instanceof ShapedCollectionType);

        $this->assertInstanceOf(ShapedCollectionType::class, $subType);
        $this->assertEquals('array', $subType->getTypeName());
        $this->assertIsArray($subType->getShapes());
        $this->assertCount(2, $subType->getShapes());

        $shape = $subType->getShapes()[0];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('h', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(StringType::class, $shape->getType());

        $shape = $subType->getShapes()[1];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('t', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(MixedType::class, $shape->getType());

        $subType = $response->getSubType();

        assert($subType instanceof ShapedCollectionType);

        $this->assertInstanceOf(ShapedCollectionType::class, $subType);

        $shape = $subType->getShapes()[2];

        $this->assertInstanceOf(StringType::class, $shape->getType());
        $this->assertEquals('a', $shape->getKey());
        $this->assertFalse($shape->isOptional());

        $shape = $subType->getShapes()[3];

        $this->assertInstanceOf(MixedType::class, $shape->getType());
        $this->assertEquals('1', $shape->getKey());
        $this->assertFalse($shape->isOptional());

        $shape = $subType->getShapes()[4];

        assert($shape instanceof ShapedCollectionItem);

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('array', $shape->getKey());
        $this->assertFalse($shape->isOptional());

        $this->assertInstanceOf(ShapedCollectionType::class, $shape->getType());
        $this->assertEquals('array3', $shape->getType()->getTypeName());

        $subType = $shape->getType();

        assert($subType instanceof ShapedCollectionType);

        $this->assertCount(3, $subType->getShapes());

        $shape = $subType->getShapes()[0];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('age', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(IntType::class, $shape->getType());

        $shape = $subType->getShapes()[1];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('name', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(StringType::class, $shape->getType());


        $shape = $subType->getShapes()[2];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('hobbies', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(ShapedCollectionType::class, $shape->getType());

        $subType = $shape->getType();

        assert($subType instanceof ShapedCollectionType);

        $this->assertEquals('array4', $subType->getTypeName());
        $this->assertCount(2, $subType->getShapes());
        $this->assertIsArray($subType->getShapes());


        $shape = $subType->getShapes()[0];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('0', $shape->getKey());
        $this->assertFalse($shape->isOptional());
        $this->assertInstanceOf(StringType::class, $shape->getType());

        $shape = $subType->getShapes()[1];

        $this->assertInstanceOf(ShapedCollectionItem::class, $shape);
        $this->assertEquals('1', $shape->getKey());
        $this->assertTrue($shape->isOptional());
        $this->assertInstanceOf(UnionType::class, $shape->getType());

        $unionType = $shape->getType();

        assert($unionType instanceof UnionType);

        $this->assertIsArray($unionType->getTypes());
        $this->assertCount(2, $unionType->getTypes());
        $this->assertInstanceOf(StringType::class, $unionType->getTypes()[0]);
        $this->assertInstanceOf(IntType::class, $unionType->getTypes()[1]);
    }

    /**
     * @throws FailedToParseDocblockToTypeException
     */
    public function testItCanParseUnionWithArray(): void
    {
        $typeString = 'string|null|mixed[]|Collection<string>|array<string>|array<int, string>|array<int, (string|int)>|array<int, array{remco: string|int, testing: string|int, a: string, 1: mixed, 1: mixed, array: array{age: int, name: string, hobbies: array{0: string, 1: string}}}>';
        //        $typeString = 'array1<int, array2{remco: string|int, normal: array3{t: string, a: array{remco: string, smits: string}}, a: string, geen: array{groen: int, aarde: string}}>';
//        $typeString = 'array1<int, array2{1: array3{2: array{remco: string, smits: string}}, a: string, geen: array{aarde: string}}>';

        $response = TypeParser::parse($typeString);

        $this->assertInstanceOf(UnionType::class, $response);

        assert($response instanceof UnionType);

        $types = $response->getTypes();

        $this->assertInstanceOf(StringType::class, $types[0]);
        $this->assertInstanceOf(NullType::class, $types[1]);

        $this->assertInstanceOf(CollectionType::class, $types[2]);
        assert($types[2] instanceof CollectionType);
        $this->assertInstanceOf(MixedType::class, $types[2]->getSubType());

        dd($types[2]);
    }
}