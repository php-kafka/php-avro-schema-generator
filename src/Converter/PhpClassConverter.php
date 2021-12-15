<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Converter;

use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClass;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;

class PhpClassConverter implements PhpClassConverterInterface
{
    private ClassParserInterface $parser;

    /**
     * @param ClassParserInterface $parser
     */
    public function __construct(ClassParserInterface $parser)
    {
        $this->parser = $parser;
    }


    public function convert(string $phpClass): ?PhpClassInterface
    {
        $this->parser->setCode($phpClass);

        if (null === $this->parser->getClassName()) {
            return null;
        }

        $convertedProperties = $this->getConvertedProperties($this->parser->getProperties());

        return new PhpClass(
            $this->parser->getClassName(),
            $this->parser->getNamespace(),
            $phpClass,
            $convertedProperties
        );
    }

    /**
     * @param PhpClassPropertyInterface[] $properties
     * @return PhpClassPropertyInterface[]
     */
    private function getConvertedProperties(array $properties): array
    {
        $convertedProperties = [];
        foreach ($properties as $property) {
            $convertedType = $this->getConvertedType($property->getPropertyType());
            $convertedProperties[] = new PhpClassProperty(
                $property->getPropertyName(),
                $convertedType,
                $property->getPropertyDefault(),
                $property->getPropertyDoc(),
                $property->getPropertyLogicalType()
            );
        }

        return $convertedProperties;
    }

    private function getConvertedType(string $type): string
    {

        return $type;
    }
}
