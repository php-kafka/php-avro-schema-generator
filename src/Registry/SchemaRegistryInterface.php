<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Registry;

use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;

interface SchemaRegistryInterface
{
    /**
     * @param  string $schemaTemplateDirectory
     * @return SchemaRegistryInterface
     */
    public function addSchemaTemplateDirectory(string $schemaTemplateDirectory): SchemaRegistryInterface;

    /**
     * @return array<int,SchemaTemplateInterface>
     */
    public function getRootSchemas(): array;

    /**
     * @return array<string,int>
     */
    public function getSchemaDirectories(): array;

    /**
     * @return SchemaRegistryInterface
     */
    public function load(): SchemaRegistryInterface;

    /**
     * @return array<string, SchemaTemplateInterface>
     */
    public function getSchemas(): array;

    /**
     * @param string $namespace
     * @return array<string>
     */
    public function getSchemaNamesPerNamespace(string $namespace): array;

    /**
     * @param  string $schemaId
     * @return SchemaTemplateInterface|null
     */
    public function getSchemaById(string $schemaId): ?SchemaTemplateInterface;


    /**
     * @param array<string,string> $schemaData
     * @param SchemaTemplateInterface $template
     * @return string
     */
    public function getSchemaId(array $schemaData, SchemaTemplateInterface $template): string;
}
