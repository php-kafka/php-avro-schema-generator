<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

use PhpKafka\PhpAvroSchemaGenerator\Parser\PropertyAttributesInterface;

final class PhpClassProperty implements PhpClassPropertyInterface
{
    /** @var mixed */
    private $propertyDefault;
    private ?string $propertyDoc;
    private ?string $propertyLogicalType;
    private string $propertyName;

    /** @var string|string[]  */
    private $propertyType;

    /**
     * @param string $propertyName
     * @param string[]|string $propertyType
     * @param null|mixed $propertyDefault
     * @param null|string $propertyDoc
     * @param null|string $propertyLogicalType
     */
    public function __construct(
        string $propertyName,
        $propertyType,
        $propertyDefault = self::NO_DEFAULT,
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

    /**
     * @return string[]|string
     */
    public function getPropertyType()
    {
        return $this->propertyType;
    }
}
