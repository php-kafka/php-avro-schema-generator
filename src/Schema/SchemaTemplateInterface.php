<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Schema;

interface SchemaTemplateInterface
{
    /**
     * @return string
     */
    public function getSchemaDefinition(): string;

    /**
     * @return string
     */
    public function getSchemaLevel(): string;

    /**
     * @return string
     */
    public function getSchemaId(): string;

    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @param  string $schemaId
     * @return SchemaTemplateInterface
     */
    public function withSchemaId(string $schemaId): SchemaTemplateInterface;

    /**
     * @param  string $schemaDefinition
     * @return SchemaTemplateInterface
     */
    public function withSchemaDefinition(string $schemaDefinition): SchemaTemplateInterface;

    /**
     * @param  string $schemaLevel
     * @return SchemaTemplateInterface
     */
    public function withSchemaLevel(string $schemaLevel): SchemaTemplateInterface;

    /**
     * @param  string $filename
     * @return SchemaTemplateInterface
     */
    public function withFilename(string $filename): SchemaTemplateInterface;

    /**
     * @return bool
     */
    public function isPrimitive(): bool;
}
