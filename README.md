# Avro schema generator for PHP
[![Actions Status](https://github.com/php-kafka/php-avro-schema-generator/workflows/CI/badge.svg)](https://github.com/php-kafka/php-avro-schema-generator/workflows/CI/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/41aecf21566d7e9bfb69/maintainability)](https://codeclimate.com/github/php-kafka/php-avro-schema-generator/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/41aecf21566d7e9bfb69/test_coverage)](https://codeclimate.com/github/php-kafka/php-avro-schema-generator/test_coverage)
![Supported PHP versions: 7.4 .. 8.x](https://img.shields.io/badge/php-7.4%20..%208.x-blue.svg)
[![Latest Stable Version](https://poser.pugx.org/php-kafka/php-avro-schema-generator/v/stable)](https://packagist.org/packages/php-kafka/php-avro-schema-generator)

## Installation
```
composer require php-kafka/php-avro-schema-generator "^3.0"
```

## Description
This library enables you to:
- Manage your embedded schema as separate files
- The library is able to merge those files
- The library is able to generate avsc schema templates from PHP classes

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

$merger = new SchemaMerger('./schema');
$merger->setSchemaRegistry($registry);

$merger->merge();

```

### Merge optimizers
There are optimizers that you can enable for merging schema:  
- FullNameOptimizer: removes unneeded namespaces
- FieldOrderOptimizer: the first fields of a record schema will be: type, name, namespace (if present)
- PrimitiveSchemaOptimizer: Optimizes primitive schema e.g. `{"type": "string"}` to `"string"`

How to enable optimizer:  

**Console example**
```bash
./vendor/bin/avro-cli --optimizeFullNames --optimizeFieldOrder --optimizePrimitiveSchemas avro:subschema:merge ./example/schemaTemplates ./example/schema
```
**PHP Example**
```php
<?php

use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMerger;
use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FieldOrderOptimizer;
use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FullNameOptimizer;
use PhpKafka\PhpAvroSchemaGenerator\Optimizer\PrimitiveSchemaOptimizer;

$registry = (new SchemaRegistry())
    ->addSchemaTemplateDirectory('./schemaTemplates')
    ->load();

$merger = new SchemaMerger('./schema');
$merger->setSchemaRegistry($registry);
$merger->addOptimizer(new FieldOrderOptimizer());
$merger->addOptimizer(new FullNameOptimizer());
$merger->addOptimizer(new PrimitiveSchemaOptimizer());

$merger->merge();

```

### Generating schemas from classes
You will need to adjust the generated templates, but it gives you a good starting point to work with.  
Class directories: Directories containing the classes you want to generate schemas from  
Output directory: output directory for your generated schema templates  
After you have reviewed and adjusted your templates you will need to merge them (see above)  

**Console example**
```bash
./vendor/bin/avro-cli avro:schema:generate ./example/classes ./example/schemaTemplates
```

**PHP Example**
```php
<?php

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverter;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpParser\ParserFactory;

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$classPropertyParser = new ClassPropertyParser(new DocCommentParser());
$classParser = new ClassParser($parser, $classPropertyParser);

$converter = new PhpClassConverter($classParser);
$registry = (new ClassRegistry($converter))->addClassDirectory('./classes')->load();

$generator = new SchemaGenerator('./schema');
$generator->setClassRegistry($registry);
$schemas = $generator->generate();
$generator->exportSchemas($schemas);
```
The generator is able to detect types from:
- doc comments
- property types
- doc annotations
  - @avro-type to set a fixed type instead of calculating one
  - @avro-default set a default for this property in your schema
  - @avro-doc to set schema doc comment
  - @avro-logical-type set logical type for your property (decimal is not yet supported, since it has additional parameters)

## Disclaimer
In `v1.3.0` the option `--optimizeSubSchemaNamespaces` was added. It was not working fully  
in the `1.x` version and we had some discussions ([#13](https://github.com/php-kafka/php-avro-schema-generator/issues/13)) about it.  
Ultimately the decision was to adapt this behaviour fully in `v2.0.0` so you might want to  
upgrade if you rely on that behaviour.
