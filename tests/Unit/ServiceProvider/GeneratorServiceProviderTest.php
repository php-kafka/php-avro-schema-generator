<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGeneratorInterface;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\GeneratorServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\GeneratorServiceProvider
 */
class GeneratorServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $container = new Container();

        (new GeneratorServiceProvider())->register($container);

        self::assertTrue(isset($container[SchemaGeneratorInterface::class]));
        self::assertInstanceOf(SchemaGeneratorInterface::class, $container[SchemaGeneratorInterface::class]);
    }
}