<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverterInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\RegistryServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\RegistryServiceProvider
 */
class RegistryServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $container = new Container();
        $container[PhpClassConverterInterface::class] = $this->getMockForAbstractClass(PhpClassConverterInterface::class);

        (new RegistryServiceProvider())->register($container);

        self::assertTrue(isset($container[ClassRegistryInterface::class]));
        self::assertInstanceOf(ClassRegistryInterface::class, $container[ClassRegistryInterface::class]);
        self::assertTrue(isset($container[SchemaRegistryInterface::class]));
        self::assertInstanceOf(SchemaRegistryInterface::class, $container[SchemaRegistryInterface::class]);
    }
}
