<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

use PhpKafka\PhpAvroSchemaGenerator\Parser\PropertyAttributesInterface;

class PhpClassProperty implements PhpClassPropertyInterface
{
    /** @var mixed */
    private $propertyDefault;
    private ?string $propertyDoc;
    private ?string $propertyLogicalType;
    private string $propertyName;
    private string $propertyType;

    /**
     * @param mixed $propertyDefault
     * @param string $propertyDoc
     * @param string $propertyLogicalType
     * @param string $propertyName
     * @param string $propertyType
     */
    public function __construct(
        string $propertyName,
        string $propertyType,
        mixed $propertyDefault = self::NO_DEFAULT,
        ?string $propertyDoc = null,
        ?string $propertyLogicalType = null
    ) {
        $this->propertyDefault = $propertyDefault;
        $this->propertyDoc = $propertyDoc;
        $this->propertyLogicalType = $propertyLogicalType;
        $this->propertyName = $propertyName;
        $this->propertyType = $propertyType;
    }

    /**
     * @return mixed
     */
    public function getPropertyDefault()
    {
        return $this->propertyDefault;
    }

    public function getPropertyDoc(): ?string
    {
        return $this->propertyDoc;
    }

    public function getPropertyLogicalType(): ?string
    {
        return $this->propertyLogicalType;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getPropertyType(): string
    {
        return $this->propertyType;
    }
}
