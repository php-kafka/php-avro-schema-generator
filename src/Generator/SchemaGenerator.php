<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Generator;

use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroRecordInterface;
use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroFieldInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use RuntimeException;

final class SchemaGenerator implements SchemaGeneratorInterface
{
    private string $outputDirectory;

    /**
     * @var ?ClassRegistryInterface
     */
    private ?ClassRegistryInterface $classRegistry;

    public function __construct(string $outputDirectory = '/tmp')
    {
        $this->outputDirectory = $outputDirectory;
    }

    /**
     * @return ClassRegistryInterface|null
     */
    public function getClassRegistry(): ?ClassRegistryInterface
    {
        return $this->classRegistry;
    }

    /**
     * @param ClassRegistryInterface $classRegistry
     */
    public function setClassRegistry(ClassRegistryInterface $classRegistry): void
    {
        $this->classRegistry = $classRegistry;
    }

    /**
     * @return string
     */
    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    /**
     * @param string $outputDirectory
     */
    public function setOutputDirectory(string $outputDirectory): void
    {
        $this->outputDirectory = $outputDirectory;
    }

    /**
     * @return array<string,string|false>
     */
    public function generate(): array
    {
        $schemas = [];

        if (null === $this->getClassRegistry()) {
            throw new RuntimeException('Please set a ClassRegistry for the generator');
        }

        /** @var AvroRecordInterface $class */
        foreach ($this->getClassRegistry()->getClasses() as $class) {
            $schema = [];
            $schema['type'] = 'record';
            $schema['name'] = $class->getRecordName();
            if (null !== $this->convertNamespace($class->getRecordNamespace())) {
                $schema['namespace'] = $this->convertNamespace($class->getRecordNamespace());
            }
            $schema['fields'] = [];

            /** @var AvroFieldInterface $property */
            foreach ($class->getRecordFields() as $property) {
                $field = $this->getFieldForProperty($property);
                $schema['fields'][] = $field;
            }

            if (false === isset($schema['namespace'])) {
                $namespace = $schema['name'];
            } else {
                $namespace = $schema['namespace'] . '.' . $schema['name'];
            }

            $schemas[$namespace] = json_encode($schema);
        }

        return $schemas;
    }

    /**
     * @param AvroFieldInterface $property
     * @return array<string, mixed>
     */
    private function getFieldForProperty(AvroFieldInterface $property): array
    {
        $field = ['name' => $property->getFieldName()];
        $field['type'] = $property->getFieldType();

        if (AvroFieldInterface::NO_DEFAULT !== $property->getFieldDefault()) {
            $field['default'] = $property->getFieldDefault();
        }

        if (null !== $property->getFieldDoc() && '' !== $property->getFieldDoc()) {
            $field['doc'] = $property->getFieldDoc();
        }

        if (null !== $property->getFieldLogicalType()) {
            $field['logicalType'] = $property->getFieldLogicalType();
        }

        return $field;
    }

    /**
     * @param array<string,string|false> $schemas
     * @return int
     */
    public function exportSchemas(array $schemas): int
    {
        $fileCount = 0;

        foreach ($schemas as $schemaName => $schema) {
            $filename = $this->getSchemaFilename($schemaName);
            file_put_contents($filename, $schema);
            ++$fileCount;
        }

        return $fileCount;
    }

    /**
     * @param string $schemaName
     * @return string
     */
    private function getSchemaFilename(string $schemaName): string
    {
        return $this->getOutputDirectory() . '/' . $schemaName . '.' . Avro::FILE_EXTENSION;
    }

    /**
     * @param string|null $namespace
     * @return string|null
     */
    private function convertNamespace(?string $namespace): ?string
    {
        if (null === $namespace) {
            return null;
        }

        return str_replace('\\', '.', $namespace);
    }
}
