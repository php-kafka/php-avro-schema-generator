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
 * @covers \PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser
 */
class ClassParserTest extends TestCase
{
    public function testGetClassName(): void
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode((string) file_get_contents($filePath));
        self::assertEquals('SomeTestClass', $parser->getClassName());
        self::assertEquals('SomeTestClass', $parser->getClassName());
    }

    public function testGetClassNameForInterface(): void
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestInterface.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode((string) file_get_contents($filePath));
        self::assertNull($parser->getClassName());
    }

    public function testGetNamespace(): void
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode((string) file_get_contents($filePath));
        self::assertEquals('PhpKafka\\PhpAvroSchemaGenerator\\Example', $parser->getNamespace());
        self::assertEquals('PhpKafka\\PhpAvroSchemaGenerator\\Example', $parser->getNamespace());
    }

    public function testGetProperties(): void
    {
        $filePath = __DIR__ . '/../../../example/classes/SomeTestClass.php';
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode((string) file_get_contents($filePath));
        $properties = $parser->getProperties();
        self::assertCount(16, $properties);

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
        $refProperty->setAccessible(true);
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

    public function testClassWithNullableType(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    public ?string $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertEquals('null|string', $properties[0]->getPropertyType());
    }

    public function testClassWithUnionType(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    public int|string $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertEquals('int|string', $properties[0]->getPropertyType());
    }

    public function testClassWithDocUnionType(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    /**
                     * @var int|string
                     */
                    public $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertEquals('int|string', $properties[0]->getPropertyType());
    }

    public function testClassWithAnnotations(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    /**
                     * @avro-type string
                     * @avro-default abc def
                     * @avro-doc some doc bla bla
                     * @var int|string
                     */
                    public $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertEquals('string', $properties[0]->getPropertyType());
        self::assertEquals('abc def', $properties[0]->getPropertyDefault());
        self::assertEquals('some doc bla bla', $properties[0]->getPropertyDoc());

    }

    public function testClassWithNoParentFile(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('<?php class foo extends \RuntimeException {private $x;}');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertEquals('string', $properties[0]->getPropertyType());
    }
}
