<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Merger;

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
     * @param  SchemaTemplateInterface $schemaTemplate
     * @return SchemaTemplateInterface
     */
    public function getResolvedSchemaTemplate(SchemaTemplateInterface $schemaTemplate): SchemaTemplateInterface;

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
     * @param  array<string,mixed> $schemaDefinition
     * @return array<string,mixed>
     */
    public function transformExportSchemaDefinition(array $schemaDefinition): array;
}
