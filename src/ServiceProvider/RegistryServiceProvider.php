<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverterInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class RegistryServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[ClassRegistryInterface::class] = static function (Container $container): ClassRegistryInterface {
            return new ClassRegistry($container[PhpClassConverterInterface::class]);
        };

        $container[SchemaRegistryInterface::class] = static function (): SchemaRegistryInterface {
            return new SchemaRegistry();
        };
    }
}
