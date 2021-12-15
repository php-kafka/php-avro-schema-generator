<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;

interface ClassPropertyParserInterface
{
    public function parseProperty($property): PhpClassPropertyInterface;
}