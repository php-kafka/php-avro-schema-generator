<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

class PhpClassProperty implements PhpClassPropertyInterface
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string
     */
    private $propertyType;

    /**
     * @var string|null
     */
    private $propertyArrayType;

    public function __construct(string $propertyName, string $propertyType, ?string $propertyArrayType)
    {
        $this->propertyName = $propertyName;
        $this->propertyType = $propertyType;
        $this->propertyArrayType = $propertyArrayType;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    /**
     * @return string|null
     */
    public function getPropertyArrayType(): ?string
    {
        return $this->propertyArrayType;
    }
}
