<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Avro;

final class AvroField implements AvroFieldInterface
{
    /** @var mixed */
    private $fieldDefault;
    private ?string $fieldDoc;
    private ?string $fieldLogicalType;
    private string $fieldName;

    /** @var string|string[]  */
    private $fieldType;

    /**
     * @param string $fieldName
     * @param string[]|string $fieldType
     * @param null|mixed $fieldDefault
     * @param null|string $fieldDoc
     * @param null|string $fieldLogicalType
     */
    public function __construct(
        string  $fieldName,
                $fieldType,
                $fieldDefault = self::NO_DEFAULT,
        ?string $fieldDoc = null,
        ?string $fieldLogicalType = null
    ) {
        $this->fieldDefault = $fieldDefault;
        $this->fieldDoc = $fieldDoc;
        $this->fieldLogicalType = $fieldLogicalType;
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
    }

    /**
     * @return mixed
     */
    public function getFieldDefault()
    {
        return $this->fieldDefault;
    }

    public function getFieldDoc(): ?string
    {
        return $this->fieldDoc;
    }

    public function getFieldLogicalType(): ?string
    {
        return $this->fieldLogicalType;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string[]|string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }
}
