<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

interface PhpClassPropertyInterface
{
    public const NO_DEFAULT = 'there-was-no-default-set';

    /**
     * @return mixed
     */
    public function getPropertyDefault();

    public function getPropertyDoc(): ?string;

    public function getPropertyLogicalType(): ?string;

    public function getPropertyName(): string;

    public function getPropertyType(): string;
}
