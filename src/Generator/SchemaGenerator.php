<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Generator;

use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use RuntimeException;

final class SchemaGenerator implements SchemaGeneratorInterface
{
    private string $outputDirectory;

    /**
     * @var ClassRegistryInterface
     */
    private ClassRegistryInterface $classRegistry;

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

        /** @var PhpClassInterface $class */
        foreach ($this->getClassRegistry()->getClasses() as $class) {
            $schema = [];
            $schema['type'] = 'record';
            $schema['name'] = $class->getClassName();
            $schema['namespace'] = $this->convertNamespace($class->getClassNamespace());
            $schema['fields'] = [];

            /** @var PhpClassPropertyInterface $property */
            foreach ($class->getClassProperties() as $property) {
                $field = $this->getFieldForProperty($property);
                $schema['fields'][] = $field;
            }

            $namespace = $schema['namespace'] . '.' . $schema['name'];

            if (null === $schema['namespace']) {
                $namespace = $schema['name'];
            }

            $schemas[$namespace] = json_encode($schema);
        }

        return $schemas;
    }

    /**
     * @param PhpClassPropertyInterface $property
     * @return array<string, mixed>
     */
    private function getFieldForProperty(PhpClassPropertyInterface $property): array
    {
        $field = ['name' => $property->getPropertyName()];
        $field['type'] = $property->getPropertyType();

        if (PhpClassPropertyInterface::NO_DEFAULT !== $property->getPropertyDefault()) {
            $field['default'] = $property->getPropertyDefault();
        }

        if (null !== $property->getPropertyDoc() && '' !== $property->getPropertyDoc()) {
            $field['doc'] = $property->getPropertyDoc();
        }

        if (null !== $property->getPropertyLogicalType()) {
            $field['logicalType'] = $property->getPropertyLogicalType();
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
