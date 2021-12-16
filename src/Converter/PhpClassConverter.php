<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Converter;

use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClass;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;

class PhpClassConverter implements PhpClassConverterInterface
{
    private ClassParserInterface $parser;

    /**
     * @var array<string,int>
     */
    private array $typesToSkip = [
        'null' => 1,
        'object' => 1,
        'callable' => 1,
        'resource' => 1,
        'mixed' => 1
    ];

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

            if (null === $convertedType) {
                continue;
            }

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

    private function getConvertedType(string $type)
    {
        $types = explode('|', $type);

        if (1 === count($types)) {
            return $this->getFullTypeName($type);
        }

        return $this->getConvertedUnionType($types);
    }

    private function getFullTypeName(string $type): ?string
    {
        if (true === isset(Avro::MAPPED_TYPES[$type])) {
            $type = Avro::MAPPED_TYPES[$type];
        }

        if (true === isset($this->typesToSkip[$type])) {
            return null;
        }

        if (true === isset(Avro::BASIC_TYPES[$type])) {
            return $type;
        }

        $usedClasses = $this->parser->getUsedClasses();

        if (true === isset($usedClasses[$type])) {
            return $usedClasses[$type];
        }

        if (null !== $this->parser->getNamespace()) {
            return $this->parser->getNamespace() . '\\' . $type;
        }

        return $type;
    }

    private function getConvertedUnionType(array $types): array
    {
        $convertedUnionType = [];

        foreach ($types as $type) {
            if (true === isset($this->typesToSkip[$type])) {
                continue;
            }

            if (false === $this->isArrayType($type)) {
                $convertedUnionType[] = $type;
            }
        }

        $arrayType = $this->getArrayType($types);

        if (0 !== count($convertedUnionType) && [] !== $arrayType) {
            $convertedUnionType[] = $arrayType;
        } else {
            return $arrayType;
        }

        return $convertedUnionType;
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function getArrayType(array $types): array
    {
        $arrayTypes = [];
        $itemPrefix = '[';
        $itemSuffix = ']';

        foreach ($types as $type) {
            if (true === $this->isArrayType($type)) {
                $arrayTypes[] = $type;
            }
        }

        if (0 === count($arrayTypes)) {
            return [];
        }

        foreach ($arrayTypes as $idx => $arrayType) {
            if ('array' === $arrayType) {
                unset($arrayTypes[$idx]);
                continue;
            }

            $cleanedType = str_replace('[]', '', $arrayType);
            $arrayTypes[$idx] = $this->getFullTypeName($cleanedType);
        }

        if (0 === count($arrayTypes)) {
            $arrayTypes[] = 'string';
        }

        if (1 === count($arrayTypes)) {
            $itemPrefix = '';
            $itemSuffix = '';
        }

        return [
            'type' => 'array',
            'items' => $itemPrefix . implode(',', $arrayTypes) . $itemSuffix
        ];
    }

    private function isArrayType(string $type): bool
    {
        if ('array' === $type || str_contains($type, '[]')) {
            return true;
        }

        return false;
    }
}
