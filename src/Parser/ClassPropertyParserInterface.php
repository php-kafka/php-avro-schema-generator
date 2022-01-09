<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroFieldInterface;
use PhpParser\Node\Stmt\Property;

interface ClassPropertyParserInterface
{
    /**
     * @param Property|mixed $property
     * @return AvroFieldInterface
     */
    public function parseProperty($property): AvroFieldInterface;
}
