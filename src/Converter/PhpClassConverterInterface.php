<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Converter;

use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroRecordInterface;

interface PhpClassConverterInterface
{
    public function convert(string $phpClass): ?AvroRecordInterface;
}
