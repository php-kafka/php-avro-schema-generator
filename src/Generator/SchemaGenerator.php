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
    /**
     * @var int[]
     */
    private array $typesToSkip = [
        'null' => 1,
        'object' => 1,
        'callable' => 1,
        'resource' => 1,
        'mixed' => 1
    ];

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
     * @return ClassRegistryInterface
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
     * @return string
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
                if (true === isset($this->typesToSkip[$property->getPropertyType()])) {
                    continue;
                }

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

                $schema['fields'][] = $field;
            }

            $schemas[$schema['namespace'] . '.' . $schema['name']] = json_encode($schema);
        }

        return $schemas;
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
     * @param string $namespace
     * @return string
     */
    private function convertNamespace(string $namespace): string
    {
        return str_replace('\\', '.', $namespace);
    }
}
