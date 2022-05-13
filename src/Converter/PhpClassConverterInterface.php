<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Converter;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;

interface PhpClassConverterInterface
{
    public function convert(string $phpClass): ?PhpClassInterface;
}
