<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemcoSmits\Hydrate\Cache\HydraterCache;
use RemcoSmits\Hydrate\Docblock\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\Exception\AbstractHydrateException;
use RemcoSmits\Hydrate\Exception\ClassDoesntExistsException;
use RemcoSmits\Hydrate\Exception\HydrateFailedException;
use RemcoSmits\Hydrate\Exception\InvalidDataTypeException;
use RemcoSmits\Hydrate\Exception\ValueWasNotFoundException;
use RemcoSmits\Hydrate\Hydrater;
use RemcoSmits\Hydrate\Reflection\HydrateReflectionClass;
use RemcoSmits\Hydrate\Tests\Unit\TestClasses\SubClass;
use RemcoSmits\Hydrate\Tests\Unit\TestClasses\TestClass;

class HydrateCacheTest extends TestCase
{
    /**
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws FailedToParseDocblockToTypeException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ValueWasNotFoundException
     */
    public function testItCanFindHydrateReflectionClass(): void
    {
        Hydrater::to(TestClass::class, [
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

        $this->assertTrue(HydraterCache::has(TestClass::class));
        $this->assertTrue(HydraterCache::has(SubClass::class));

        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(TestClass::class));
        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(SubClass::class));
    }

    /**
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ValueWasNotFoundException
     * @throws FailedToParseDocblockToTypeException
     */
    public function testItCanDeleteHydrateCacheItem(): void
    {
        $this->assertTrue(HydraterCache::has(TestClass::class));
        $this->assertTrue(HydraterCache::has(SubClass::class));

        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(TestClass::class));
        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(SubClass::class));

        HydraterCache::delete(TestClass::class);
        HydraterCache::delete(SubClass::class);

        $this->assertFalse(HydraterCache::has(TestClass::class));
        $this->assertFalse(HydraterCache::has(SubClass::class));

        $this->assertNull(HydraterCache::get(TestClass::class));
        $this->assertNull(HydraterCache::get(SubClass::class));

        Hydrater::to(TestClass::class, [
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

        $this->assertTrue(HydraterCache::has(TestClass::class));
        $this->assertTrue(HydraterCache::has(SubClass::class));

        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(TestClass::class));
        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(SubClass::class));
    }

    public function testItCantUnserializeInvalidFileContent(): void
    {
        $this->assertTrue(HydraterCache::has(TestClass::class));
        $this->assertTrue(HydraterCache::has(SubClass::class));

        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(TestClass::class));
        $this->assertInstanceOf(HydrateReflectionClass::class, HydraterCache::get(SubClass::class));

        file_put_contents(HydraterCache::formatToFileName(TestClass::class), 'invalid_serialized_content');

        $this->assertTrue(HydraterCache::has(TestClass::class));
        $this->assertTrue(HydraterCache::has(SubClass::class));

        $this->assertNull(HydraterCache::get(TestClass::class));
    }

    /**
     * @throws AbstractHydrateException
     * @throws ClassDoesntExistsException
     * @throws FailedToParseDocblockToTypeException
     * @throws HydrateFailedException
     * @throws InvalidDataTypeException
     * @throws ValueWasNotFoundException
     */
    public function testItCannotReadClassThatIsNotHydrateReflectionClass(): void
    {
        Hydrater::to(TestClass::class, [
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

        file_put_contents(HydraterCache::formatToFileName(TestClass::class), serialize(new TestClass()));

        $this->assertNull(HydraterCache::get(TestClass::class));
    }
}