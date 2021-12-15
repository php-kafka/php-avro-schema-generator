<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGeneratorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GeneratorServiceProvider implements ServiceProviderInterface
{

    public function register(Container $container)
    {
        $container[SchemaGeneratorInterface::class] = static function (Container $container): SchemaGeneratorInterface {
            return new SchemaGenerator();
        };
    }
}