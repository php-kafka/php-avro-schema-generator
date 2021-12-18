<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMerger;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMergerInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MergerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[SchemaMergerInterface::class] = static function (): SchemaMergerInterface {
            return new SchemaMerger();
        };
    }
}
