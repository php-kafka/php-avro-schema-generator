<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverterInterface;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\ConverterServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\ConverterServiceProvider
 */
class ConverterServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $container = new Container();
        $container[ClassParserInterface::class] = $this->getMockForAbstractClass(ClassParserInterface::class);

        (new ConverterServiceProvider())->register($container);

        self::assertTrue(isset($container[PhpClassConverterInterface::class]));
        self::assertInstanceOf(PhpClassConverterInterface::class, $container[PhpClassConverterInterface::class]);
    }
}
