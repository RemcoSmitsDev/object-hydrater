<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemcoSmits\Hydrate\DocblockParser;
use RemcoSmits\Hydrate\Exception\FailedToParseDocblockToTypeException;
use RemcoSmits\Hydrate\PropertyType;

class DocblockParserTest extends TestCase
{
    /** @throws FailedToParseDocblockToTypeException */
    public function testItCanParseDocblockToType(): void
    {
        $docblock = '/** @var string|null */';

        $type = DocblockParser::getType($docblock);

        $this->assertInstanceOf(PropertyType::class, $type);

        $this->assertEquals('string', $type->getType());
        $this->assertEquals('string', $type->getFqn());
        $this->assertFalse($type->isCollection());
        $this->assertNull($type->getCollectionItemType());
        $this->assertTrue($type->isBuildIn());
        $this->assertFalse($type->isStatic());
    }
}