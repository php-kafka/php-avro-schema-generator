<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;

$registry = (new ClassRegistry())->addClassDirectory('./classes')->load();
$generator = new SchemaGenerator($registry);
$schemas = $generator->generate();
$generator->exportSchemas($schemas);
