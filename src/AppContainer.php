<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator;

use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\CommandServiceProvider;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\ConverterServiceProvider;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\GeneratorServiceProvider;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\MergerServiceProvider;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\ParserServiceProvider;
use PhpKafka\PhpAvroSchemaGenerator\ServiceProvider\RegistryServiceProvider;
use Pimple\Container;

class AppContainer
{
    /**
     * @param string $env
     * @return Container
     */
    public static function init(): Container
    {
        $container = new Container();

        $container
            ->register(new GeneratorServiceProvider())
            ->register(new MergerServiceProvider())
            ->register(new ParserServiceProvider())
            ->register(new ConverterServiceProvider())
            ->register(new RegistryServiceProvider())
            ->register(new CommandServiceProvider());


        return $container;
    }
}