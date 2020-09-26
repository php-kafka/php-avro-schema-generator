#!/usr/bin/env php
<?php

declare(strict_types=1);

if (is_file(__DIR__ . '/../../../autoload.php') === true) {
    include_once __DIR__ . '/../../../autoload.php';
} else {
    include_once __DIR__ . '/../vendor/autoload.php';
}

use Symfony\Component\Console\Application;
use NickZh\PhpAvroSchemaGenerator\Command\SubSchemaMergeCommand;
use NickZh\PhpAvroSchemaGenerator\Command\SchemaGenerateCommand;

$application = new Application();

$application->add(new SchemaGenerateCommand());
$application->add(new SubSchemaMergeCommand());

$application->run();
