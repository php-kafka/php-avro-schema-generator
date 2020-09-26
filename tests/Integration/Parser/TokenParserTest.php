<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Parser\TokenParser;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Parser\TokenParser
 */
class TokenParserTest extends TestCase
{
    public function testGetClassName()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $parser = new TokenParser(file_get_contents($filePath));
        self::assertEquals('SomeTestClass', $parser->getClassName());
        self::assertEquals('SomeTestClass', $parser->getClassName());
    }

    public function testGetClassNameForInterface()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestInterface.php';
        $parser = new TokenParser(file_get_contents($filePath));
        self::assertNull($parser->getClassName());
    }

    public function testGetNamespace()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $parser = new TokenParser(file_get_contents($filePath));
        self::assertEquals('PhpKafka\\PhpAvroSchemaGenerator\\Example', $parser->getNamespace());
        self::assertEquals('PhpKafka\\PhpAvroSchemaGenerator\\Example', $parser->getNamespace());
    }

    public function testGetProperties()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $parser = new TokenParser(file_get_contents($filePath));
        $properties = $parser->getProperties($parser->getNamespace() . '\\' . $parser->getClassName());
        self::assertCount(15, $properties);

        foreach($properties as $property) {
            self::assertInstanceOf(PhpClassPropertyInterface::class, $property);
        }
    }
}
