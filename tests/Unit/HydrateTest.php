<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Tests\Unit;

use ArrayIterator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RemcoSmits\Hydrate\Exception\AbstractHydrateException;
use RemcoSmits\Hydrate\Exception\ClassDoesntExistsException;
use RemcoSmits\Hydrate\Exception\HydrateFailedException;
use RemcoSmits\Hydrate\Exception\InvalidDataTypeException;
use RemcoSmits\Hydrate\Exception\ValueWasNotFoundException;
use RemcoSmits\Hydrate\Hydrater;
use RemcoSmits\Hydrate\Tests\Unit\TestClasses\Collection;
use RemcoSmits\Hydrate\Tests\Unit\TestClasses\SubClass;
use RemcoSmits\Hydrate\Tests\Unit\TestClasses\TestClass;
use Throwable;

class HydrateTest extends TestCase
{
    public function testCanHydrateClass(): TestClass
    {
        try {
            $response = Hydrater::to(TestClass::class, [
                'name' => 'remco',
                'subClass' => ['age' => 10],
                'date' => '1987-11-24',
                'items' => [
                    ['age' => 10],
                    ['age' => 11],
                ],
                'collection' => [
                    ['age' => 99]
                ],
                'propWithoutDeclaredType' => [['age' => '10']],
                'iterator' => [['age' => 9]],
                'array_of_ints' => ['1', '3'],
                'array_iterator_ints' => ['10', 2],
                'array_iterator_strings' => [2, 'remco']
            ]);
        } catch (Throwable $throwable) {
            $this->fail($throwable->getMessage());
        }

        $this->assertTrue(true);

        return $response;
    }

    /**
     * @depends testCanHydrateClass
     */
    public function testGetCorrectResponse(TestClass $response): void
    {
        $this->assertInstanceOf(TestClass::class, $response);

        $this->assertIsString($response->getName());
        $this->assertEquals('remco', $response->getName());

        $this->assertIsString($response->getDefaultValue());
        $this->assertEquals('default string', $response->getDefaultValue());

        $this->assertInstanceOf(SubClass::class, $response->getSubClass());
        $this->assertEquals(10, $response->getSubClass()->getAge());

        $this->assertNull($response->getAltSub());

        $this->assertInstanceOf(Collection::class, $response->getCollection());
        $this->assertCount(1, $response->getCollection()->toArray());
        $this->assertInstanceOf(SubClass::class, $response->getCollection()->toArray()[0]);
        $this->assertEquals(99, $response->getCollection()->toArray()[0]->getAge());

        $this->assertIsArray($response->getItems());
        $this->assertCount(2, $response->getItems());
        $this->assertInstanceOf(SubClass::class, $response->getItems()[0]);
        $this->assertInstanceOf(SubClass::class, $response->getItems()[1]);
        $this->assertEquals(10, $response->getItems()[0]->getAge());
        $this->assertEquals(11, $response->getItems()[1]->getAge());

        $this->assertIsArray($response->getPropWithoutDeclaredType());
        $this->assertCount(1, $response->getPropWithoutDeclaredType());
        $this->assertInstanceOf(SubClass::class, $response->getPropWithoutDeclaredType()[0]);
        $this->assertEquals(10, $response->getPropWithoutDeclaredType()[0]->getAge());

        $this->assertInstanceOf(ArrayIterator::class, $response->getIterator());
        $this->assertCount(1, $response->getIterator());
        $this->assertInstanceOf(SubClass::class, $response->getIterator()[0]);
        $this->assertEquals(9, $response->getIterator()[0]->getAge());

        $this->assertInstanceOf(DateTimeImmutable::class, $response->getDate());

        $this->assertIsArray($response->getArrayOfInts());
        $this->assertNotEmpty($response->getArrayOfInts());
        $this->assertIsInt($response->getArrayOfInts()[0]);
        $this->assertIsInt($response->getArrayOfInts()[1]);
        $this->assertEquals(1, $response->getArrayOfInts()[0]);
        $this->assertEquals(3, $response->getArrayOfInts()[1]);
    }

    /**
     * @throws AbstractHydrateException
     * @throws HydrateFailedException
     * @throws ValueWasNotFoundException
     * @throws InvalidDataTypeException
     */
    public function testItExpectsClassDoesntExistsException(): void
    {
        $this->expectException(ClassDoesntExistsException::class);

        Hydrater::to('some_unknown_class', []);
    }

    /**
     * @throws ClassDoesntExistsException
     * @throws AbstractHydrateException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     */
    public function testItExpectsMissingDataExceptionForProperty(): void
    {
        $this->expectException(ValueWasNotFoundException::class);

        Hydrater::to(TestClass::class, []);
    }

    public function testItCanPassWhenValueWasNotFoundButHasDefaultValue(): void
    {
        try {
            $response = Hydrater::to(TestClass::class, [
                'name' => 'remco',
                'date' => '1987-11-24',
                'subClass' => [], // missing data for age but it has a default value
                'items' => [
                    ['age' => 10],
                    ['age' => 11],
                ],
                'collection' => [
                    ['age' => 99]
                ],
                'propWithoutDeclaredType' => [['age' => '10']],
                'iterator' => [['age' => 9]],
                'array_of_ints' => ['1', '3'],
                'array_iterator_ints' => ['10', 2],
                'array_iterator_strings' => [2, 'remco']
            ]);
        } catch (Throwable $throwable) {
            $this->fail($throwable->getMessage());
        }

        $this->assertTrue(true);

        $this->assertEquals(89, $response->getSubClass()->getAge());
    }

    /**
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws ValueWasNotFoundException
     */
    public function testItExpectsInvalidDataTypeException(): void
    {
        $this->expectException(InvalidDataTypeException::class);

        Hydrater::to(TestClass::class, [
            'name' => ['remco'], // must be a string not and array with a string
            'subClass' => ['age' => 10],
            'date' => '1987-11-24',
            'items' => [
                ['age' => 10],
                ['age' => 11],
            ],
            'collection' => [
                ['age' => 99]
            ],
            'propWithoutDeclaredType' => [['age' => '10']],
            'iterator' => [['age' => 9]],
            'array_of_ints' => ['1', '3'],
            'array_iterator_ints' => ['10', 2],
            'array_iterator_strings' => [2, 'remco']
        ]);
    }

    /**
     * @throws ClassDoesntExistsException
     * @throws AbstractHydrateException
     * @throws HydrateFailedException
     * @throws ValueWasNotFoundException
     * @throws InvalidDataTypeException
     */
    public function testItExpectsOnInvalidDataTypeWithDefaultValueToPass(): void
    {
        $response = Hydrater::to(TestClass::class, [
            'name' => 'remco',
            'subClass' => ['age' => [10]], // age must be a type of int instead of array with ints
            'date' => '1987-11-24',
            'items' => [
                ['age' => 10],
                ['age' => 11],
            ],
            'collection' => [
                ['age' => 99]
            ],
            'propWithoutDeclaredType' => [['age' => '10']],
            'iterator' => [['age' => 9]],
            'array_of_ints' => ['1', '3'],
            'array_iterator_ints' => ['10', 2],
            'array_iterator_strings' => [2, 'remco']
        ]);

        $this->assertEquals(89, $response->getSubClass()->getAge());
    }
}