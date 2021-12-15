<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

use PhpKafka\PhpAvroSchemaGenerator\Parser\PropertyAttributesInterface;

class PhpClassProperty implements PhpClassPropertyInterface
{
    private PropertyAttributesInterface $propertyAttributes;

    public function __construct(PropertyAttributesInterface $propertyAttributes)
    {
        $this->propertyAttributes = $propertyAttributes;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyAttributes->getName();
    }

    /**
     * @return string
     */
    public function getPropertyType(): string
    {
        return $this->propertyAttributes->getTypes();
    }

    public function getPropertyDefault()
    {
        return $this->propertyAttributes->getDefault();
    }

    public function getPropertyLogicalType(): ?string
    {
        return $this->propertyAttributes->getLogicalType();
    }

    public function getPropertyDoc(): ?string
    {
        return $this->propertyAttributes->getDoc();
    }
}
