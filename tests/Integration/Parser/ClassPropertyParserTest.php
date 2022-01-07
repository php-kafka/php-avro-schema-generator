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
 * @covers PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser
 */
class ClassPropertyParserTest extends TestCase
{
    public function testNullDefaultProperty(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    /**
                     * @avro-default null
                     */
                    public $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertNull($properties[0]->getPropertyDefault());
    }

    public function testIntDefaultProperty(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    /**
                     * @avro-default 1
                     */
                    public $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertIsInt($properties[0]->getPropertyDefault());
    }

    public function testFloatDefaultProperty(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $parser->setCode('
            <?php
                class foo {
                    /**
                     * @avro-default 1.2
                     */
                    public $bla;
                }
        ');
        $properties = $parser->getProperties();
        self::assertEquals(1, count($properties));
        self::assertIsFloat($properties[0]->getPropertyDefault());
    }
}
