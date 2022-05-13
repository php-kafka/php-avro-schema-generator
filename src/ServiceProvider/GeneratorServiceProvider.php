<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGeneratorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GeneratorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[SchemaGeneratorInterface::class] = static function (): SchemaGeneratorInterface {
            return new SchemaGenerator();
        };
    }
}
