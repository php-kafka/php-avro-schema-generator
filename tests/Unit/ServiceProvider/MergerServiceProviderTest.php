<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMergerInterface;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\MergerServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\MergerServiceProvider
 */
class MergerServiceProviderTest extends TestCase
{
    public function testRegister(): void
    {
        $container = new Container();

        (new MergerServiceProvider())->register($container);

        self::assertTrue(isset($container[SchemaMergerInterface::class]));
        self::assertInstanceOf(SchemaMergerInterface::class, $container[SchemaMergerInterface::class]);
    }
}