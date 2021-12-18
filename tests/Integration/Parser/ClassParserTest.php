<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser
 */
class ClassParserTest extends TestCase
{
    public function testGetClassName()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode(file_get_contents($filePath));
        self::assertEquals('SomeTestClass', $parser->getClassName());
        self::assertEquals('SomeTestClass', $parser->getClassName());
    }

    public function testGetClassNameForInterface()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestInterface.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode(file_get_contents($filePath));
        self::assertNull($parser->getClassName());
    }

    public function testGetNamespace()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode(file_get_contents($filePath));
        self::assertEquals('PhpKafka\\PhpAvroSchemaGenerator\\Example', $parser->getNamespace());
        self::assertEquals('PhpKafka\\PhpAvroSchemaGenerator\\Example', $parser->getNamespace());
    }

    public function testGetProperties()
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode(file_get_contents($filePath));
        $properties = $parser->getProperties();
        self::assertCount(15, $properties);

        foreach($properties as $property) {
            self::assertInstanceOf(PhpClassPropertyInterface::class, $property);
        }
    }

    public function testClassAndNamespaceAreNullWithNoCode(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $refObject = new \ReflectionObject($parser);
        $refProperty = $refObject->getProperty('statements');
        $refProperty->setAccessible( true );
        $refProperty->setValue($parser, null);
        self::assertNull($parser->getClassName());
        self::assertNull($parser->getNamespace());
        self::assertNull($parser->getParentClassName());
        self::assertEquals([], $parser->getUsedClasses());
    }

    public function testClassWithNoParent(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('<?php class foo {}');
        self::assertNull($parser->getNamespace());
        self::assertNull($parser->getParentClassName());
        self::assertEquals([], $parser->getProperties());

    }

    public function testClassWithNoParentFile(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('<?php class foo extends \RuntimeException {private $x;}');
        self::assertEquals([], $parser->getProperties());

    }
}
