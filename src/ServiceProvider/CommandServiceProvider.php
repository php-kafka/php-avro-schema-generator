<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Command\SchemaGenerateCommand;
use PhpKafka\PhpAvroSchemaGenerator\Command\SubSchemaMergeCommand;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGeneratorInterface;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMergerInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CommandServiceProvider implements ServiceProviderInterface
{

    public function register(Container $container)
    {
        $container['console.commands'] = function () use ($container): array {
            $commands = [];

            $commands[SchemaGenerateCommand::class] = new SchemaGenerateCommand(
                $container[ClassRegistryInterface::class],
                $container[SchemaGeneratorInterface::class]
            );

            $commands[SubSchemaMergeCommand::class] = new SubSchemaMergeCommand(
                $container[SchemaMergerInterface::class],
                $container[SchemaRegistryInterface::class]
            );

            return $commands;
        };
    }
}