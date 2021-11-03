# Avro schema generator for PHP
[![Actions Status](https://github.com/php-kafka/php-avro-schema-generator/workflows/CI/badge.svg)](https://github.com/php-kafka/php-avro-schema-generator/workflows/CI/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/937e14c63beb08885c70/maintainability)](https://codeclimate.com/github/php-kafka/php-avro-schema-generator/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/937e14c63beb08885c70/test_coverage)](https://codeclimate.com/github/php-kafka/php-avro-schema-generator/test_coverage)
[![Latest Stable Version](https://poser.pugx.org/php-kafka/php-avro-schema-generator/v/stable)](https://packagist.org/packages/php-kafka/php-avro-schema-generator)
[![Latest Unstable Version](https://poser.pugx.org/php-kafka/php-avro-schema-generator/v/unstable)](https://packagist.org/packages/php-kafka/php-avro-schema-generator)

## Installation
```
composer require php-kafka/php-avro-schema-generator "^1.0"
```

## Description
Since avro does not support external subschemas, this is just a small
helper to unify your schemas and to create basic schemas from php classes (experimental!).

### Merging subschemas / schemas
Schema template directories: directories containing avsc template files (with subschema)
Output directory: output directory for the unified schema files

#### Merge subschemas (code)
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

#### Merge subschemas (command)
```bash
./vendor/bin/avro-cli avro:subschema:merge ./example/schemaTemplates ./example/schema
```

### Generating schemas from classes
Please note, that this feature is highly experimental.  
You probably still need to adjust the generated templates, but it gives you a basic template to work with.  
Class directories: Directories containing the classes you want to generate schemas from
Output directory: output directory for your generated schema templates

#### Generate schemas (code)
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

#### Merge subschemas (command)
```bash
./vendor/bin/avro-cli avro:schema:generate ./example/classes ./example/schemaTemplates
```
