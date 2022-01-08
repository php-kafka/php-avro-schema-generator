<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Exception\SkipPropertyException;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Node\Stmt\Property;

interface ClassPropertyParserInterface
{
    /**
     * @param Property $property
     * @param ClassParserInterface $classParser
     * @return PhpClassPropertyInterface
     * @throws SkipPropertyException Such property will then just skipped
     */
    public function parseProperty(Property $property, ClassParserInterface $classParser): PhpClassPropertyInterface;
}
