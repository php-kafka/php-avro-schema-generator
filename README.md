# Avro schema generator for PHP
[![Actions Status](https://github.com/php-kafka/php-avro-schema-generator/workflows/CI/badge.svg)](https://github.com/php-kafka/php-avro-schema-generator/workflows/CI/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/41aecf21566d7e9bfb69/maintainability)](https://codeclimate.com/github/php-kafka/php-avro-schema-generator/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/41aecf21566d7e9bfb69/test_coverage)](https://codeclimate.com/github/php-kafka/php-avro-schema-generator/test_coverage) 
[![Latest Stable Version](https://poser.pugx.org/php-kafka/php-avro-schema-generator/v/stable)](https://packagist.org/packages/php-kafka/php-avro-schema-generator)

## Installation
```
composer require php-kafka/php-avro-schema-generator "^2.0"
```

## Description
This library enables you to:
- Manage your embedded schema as separate files
- The library is able to merge those files
- The library is able to generate avsc schema from PHP classes

### Merging subschemas / schemas
Schema template directories: directories containing avsc template files (with subschema)
Output directory: output directory for the merged schema files

**Console example**
```bash
./vendor/bin/avro-cli avro:subschema:merge ./example/schemaTemplates ./example/schema
```

**PHP example**
```php
<?php

use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMerger;

$registry = (new SchemaRegistry())
    ->addSchemaTemplateDirectory('./schemaTemplates')
    ->load();

$merger = new SchemaMerger($registry, './schema');

$merger->merge();

```

### Merge optimizers
There are optimizers that you can enable for merging schema:
- FullNameOptimizer: removes unneeded namespaces
- FieldOrderOptimizer: the first fields of a record schema will be: type, name, namespace (if present)  

How to enable optimizer:  

**Console example**
```bash
./vendor/bin/avro-cli --optimizeFullNames --optimizeFieldOrder avro:subschema:merge ./example/schemaTemplates ./example/schema
```
**PHP Example**
```php
<?php

use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMerger;
use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FieldOrderOptimizer;
use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FullNameOptimizer;

$registry = (new SchemaRegistry())
    ->addSchemaTemplateDirectory('./schemaTemplates')
    ->load();

$merger = new SchemaMerger($registry, './schema');
$merger->addOptimizer(new FieldOrderOptimizer());
$merger->addOptimizer(new FullNameOptimizer());

$merger->merge();

```

### Generating schemas from classes
You will need to adjust the generated templates, but it gives you a good starting point to work with.  
Class directories: Directories containing the classes you want to generate schemas from
Output directory: output directory for your generated schema templates

**Console example**
```bash
./vendor/bin/avro-cli avro:schema:generate ./example/classes ./example/schemaTemplates
```

**PHP Example**
```php
<?php

use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;

$registry = (new ClassRegistry())
    ->addClassDirectory('./example/classes')
    ->load();

$generator = new SchemaGenerator($registry, './example/schemaTemplates');

$schemas = $generator->generate();

$generator->exportSchemas($schemas);

```

## Disclaimer
In `v1.3.0` the option `--optimizeSubSchemaNamespaces` was added. It was not working fully  
in the `1.x` version and we had some discussions (#13) about it.  
Ultimately the decision was to adapt this behaviour fully in `v2.0.0` so you might want to  
upgrade if you rely on that behaviour.
