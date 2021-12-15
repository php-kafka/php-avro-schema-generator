<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Generator;

use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;

interface SchemaGeneratorInterface
{
    /**
     * @return ClassRegistryInterface|null
     */
    public function getClassRegistry(): ?ClassRegistryInterface;

    /**
     * @param ClassRegistryInterface $classRegistry
     */
    public function setClassRegistry(ClassRegistryInterface $classRegistry): void;

    /**
     * @return string
     */
    public function getOutputDirectory(): string;

    /**
     * @param string $outputDirectory
     */
    public function setOutputDirectory(string $outputDirectory): void;

    /**
     * @return array<string,string|false>
     */
    public function generate(): array;

    /**
     * @param array<string,string|false> $schemas
     * @return int
     */
    public function exportSchemas(array $schemas): int;
}
