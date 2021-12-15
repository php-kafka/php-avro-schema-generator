<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

class PropertyAttributes implements PropertyAttributesInterface
{
    private string $types;

    /** @var mixed */
    private $default;

    private string $logicalType;

    private string $doc;

    private string $name;

    public const NO_DEFAULT = 'there-was-no-default-set';

    /**
     * @param string $name
     * @param string $types
     * @param null|mixed $default
     * @param string|null $logicalType
     * @param string|null $doc
     */
    public function __construct(string $name, string $types, $default, ?string $logicalType, ?string $doc)
    {
        $this->name = $name;
        $this->types = $types;
        $this->default = $default;
        $this->logicalType = $logicalType;
        $this->doc = $doc;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypes(): string
    {
        return $this->types;
    }

    /**
     * @return null|mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function getLogicalType(): ?string
    {
        return $this->logicalType;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }
}