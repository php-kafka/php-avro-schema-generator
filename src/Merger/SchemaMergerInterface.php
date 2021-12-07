<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Merger;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\OptimizerInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

interface SchemaMergerInterface
{

    /**
     * @return SchemaRegistryInterface
     */
    public function getSchemaRegistry(): SchemaRegistryInterface;

    /**
     * @return string
     */
    public function getOutputDirectory(): string;

    /**
     * @param  SchemaTemplateInterface $rootSchemaTemplate
     * @return SchemaTemplateInterface
     */
    public function getResolvedSchemaTemplate(SchemaTemplateInterface $rootSchemaTemplate): SchemaTemplateInterface;

    /**
     * @return int
     */
    public function merge(): int;

    /**
     * @param SchemaTemplateInterface $rootRootSchemaTemplate
     * @return void
     */
    public function exportSchema(SchemaTemplateInterface $rootRootSchemaTemplate): void;

    /**
     * @param  string|array<string,mixed> $schemaDefinition
     * @return string|array<string,mixed>
     */
    public function transformExportSchemaDefinition($schemaDefinition);

    /**
     * @param OptimizerInterface $optimizer
     */
    public function addOptimizer(OptimizerInterface $optimizer): void;
}
