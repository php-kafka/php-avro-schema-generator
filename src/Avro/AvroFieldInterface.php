<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Avro;

interface AvroFieldInterface
{
    public const NO_DEFAULT = 'there-was-no-default-set';
    public const EMPTY_STRING_DEFAULT = 'empty-string-default';


    /**
     * @return mixed
     */
    public function getFieldDefault();

    public function getFieldDoc(): ?string;

    public function getFieldLogicalType(): ?string;

    public function getFieldName(): string;

    /**
     * @return string[]|string
     */
    public function getFieldType();
}
