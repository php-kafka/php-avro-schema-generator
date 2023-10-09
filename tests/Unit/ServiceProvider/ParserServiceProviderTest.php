<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\ParserServiceProvider;
use PhpParser\ParserFactory;
use PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\ParserServiceProvider
 */
class ParserServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $container = new Container();
        $container[ParserFactory::class] = $this->getMockBuilder(ParserFactory::class)->getMock();
        $container[Parser::class] = $this->getMockForAbstractClass(Parser::class);


        (new ParserServiceProvider())->register($container);

        self::assertTrue(isset($container[DocCommentParserInterface::class]));
        self::assertInstanceOf(DocCommentParserInterface::class, $container[DocCommentParserInterface::class]);
        self::assertTrue(isset($container[ClassPropertyParserInterface::class]));
        self::assertInstanceOf(ClassPropertyParserInterface::class, $container[ClassPropertyParserInterface::class]);
        self::assertTrue(isset($container[ClassParserInterface::class]));
        self::assertInstanceOf(ClassParserInterface::class, $container[ClassParserInterface::class]);

    }
}
