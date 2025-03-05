<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\ServiceProvider;

use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParserInterface;
use PhpParser\ParserFactory;
use PhpParser\Parser;
use PhpParser\PhpVersion;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ParserServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container[ParserFactory::class] = static function (): ParserFactory {
            return new ParserFactory();
        };

        $container[Parser::class] = static function (Container $container): Parser {
            return $container[ParserFactory::class]->createForVersion(PhpVersion::fromComponents(8,2));
        };

        $container[DocCommentParserInterface::class] = static function (): DocCommentParserInterface {
            return new DocCommentParser();
        };

        $container[ClassPropertyParserInterface::class] =
            static function (Container $container): ClassPropertyParserInterface {
                return new ClassPropertyParser($container[DocCommentParserInterface::class]);
            };

        $container[ClassParserInterface::class] = static function (Container $container): ClassParserInterface {
            return new ClassParser($container[Parser::class], $container[ClassPropertyParserInterface::class]);
        };
    }
}
