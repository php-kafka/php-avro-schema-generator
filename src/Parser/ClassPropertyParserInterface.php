<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Node\Stmt\Property;

interface ClassPropertyParserInterface
{
    /**
     * @param Property|mixed $property
     * @return PhpClassPropertyInterface
     */
    public function parseProperty($property): PhpClassPropertyInterface;
}
