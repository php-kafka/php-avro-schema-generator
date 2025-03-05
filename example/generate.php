<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverter;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpParser\ParserFactory;

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$classPropertyParser = new ClassPropertyParser(new DocCommentParser());
$classParser = new ClassParser($parser, $classPropertyParser);

$converter = new PhpClassConverter($classParser);
$registry = (new ClassRegistry($converter))->addClassDirectory('./classes')->load();

$generator = new SchemaGenerator('./schema');
$generator->setClassRegistry($registry);
$schemas = $generator->generate();
$generator->exportSchemas($schemas);
