<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Merger;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\OptimizerInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

interface SchemaMergerInterface
{
    /**
     * @return SchemaRegistryInterface|null
     */
    public function getSchemaRegistry(): ?SchemaRegistryInterface;

    /**
     * @param SchemaRegistryInterface $schemaRegistry
     */
    public function setSchemaRegistry(SchemaRegistryInterface $schemaRegistry): void;

    /**
     * @return string
     */
    public function getOutputDirectory(): string;

    /**
     * @param string $outputDirectory
     */
    public function setOutputDirectory(string $outputDirectory): void;

    /**
     * @param  SchemaTemplateInterface $rootSchemaTemplate
     * @return SchemaTemplateInterface
     */
    public function getResolvedSchemaTemplate(SchemaTemplateInterface $rootSchemaTemplate): SchemaTemplateInterface;

    /**
     * @param bool $prefixWithNamespace
     * @param bool $useTemplateName
     * @return int
     */
    public function merge(bool $prefixWithNamespace = false, bool $useTemplateName = false): int;

    /**
     * @param SchemaTemplateInterface $rootRootSchemaTemplate
     * @return void
     */
    public function exportSchema(SchemaTemplateInterface $rootRootSchemaTemplate): void;

    /**
     * @param OptimizerInterface $optimizer
     */
    public function addOptimizer(OptimizerInterface $optimizer): void;
}
