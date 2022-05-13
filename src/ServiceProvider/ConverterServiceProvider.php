<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverter;
use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverterInterface;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConverterServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[PhpClassConverterInterface::class] =
            static function (Container $container): PhpClassConverterInterface {
                return new PhpClassConverter($container[ClassParserInterface::class]);
            };
    }
}
