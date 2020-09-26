<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Schema;

final class SchemaTemplate implements SchemaTemplateInterface
{
    /**
     * @var array<string,int>
     */
    public const AVRO_PRIMITIVE_TYPES = [
        'null' => 1,
        'boolean' => 1,
        'int' => 1,
        'long' => 1,
        'float' => 1,
        'double' => 1,
        'bytes' => 1,
        'string' => 1,
    ];

    /**
     * @var string
     */
    private $schemaDefinition;

    /**
     * @var string
     */
    private $schemaLevel = 'root';

    /**
     * @var string
     */
    private $schemaId;

    /**
     * @var string
     */
    private $filename;

    /**
     * @return string
     */
    public function getSchemaId(): string
    {
        return $this->schemaId;
    }

    /**
     * @return string
     */
    public function getSchemaDefinition(): string
    {
        return $this->schemaDefinition;
    }

    /**
     * @return string
     */
    public function getSchemaLevel(): string
    {
        return $this->schemaLevel;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param  string $schemaId
     * @return SchemaTemplateInterface
     */
    public function withSchemaId(string $schemaId): SchemaTemplateInterface
    {
        $new = clone $this;

        $new->schemaId = $schemaId;

        return $new;
    }

    /**
     * @param  string $schemaDefinition
     * @return SchemaTemplateInterface
     */
    public function withSchemaDefinition(string $schemaDefinition): SchemaTemplateInterface
    {
        $new = clone $this;

        $new->schemaDefinition = $schemaDefinition;

        return $new;
    }

    /**
     * @param  string $schemaLevel
     * @return SchemaTemplateInterface
     */
    public function withSchemaLevel(string $schemaLevel): SchemaTemplateInterface
    {
        $new = clone $this;

        $new->schemaLevel = $schemaLevel;

        return $new;
    }

    /**
     * @param  string $filename
     * @return SchemaTemplateInterface
     */
    public function withFilename(string $filename): SchemaTemplateInterface
    {
        $new = clone $this;

        $new->filename = $filename;

        return $new;
    }

    /**
     * @return bool
     */
    public function isPrimitive(): bool
    {
        $fields = json_decode($this->getSchemaDefinition(), true);

        if (true === isset($fields['type'])) {
            return array_key_exists($fields['type'], self::AVRO_PRIMITIVE_TYPES);
        }

        return false;
    }
}
