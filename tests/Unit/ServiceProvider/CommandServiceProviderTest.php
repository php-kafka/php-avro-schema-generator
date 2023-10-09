<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGeneratorInterface;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMergerInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\CommandServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\CommandServiceProvider
 */
class CommandServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $container = new Container();
        $container[ClassRegistryInterface::class] = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $container[SchemaGeneratorInterface::class] = $this->getMockForAbstractClass(SchemaGeneratorInterface::class);
        $container[SchemaMergerInterface::class] = $this->getMockForAbstractClass(SchemaMergerInterface::class);
        $container[SchemaRegistryInterface::class] = $this->getMockForAbstractClass(SchemaRegistryInterface::class);

        (new CommandServiceProvider())->register($container);

        self::assertTrue(isset($container['console.commands']));
        self::assertEquals(2, count($container['console.commands']));
    }
}
