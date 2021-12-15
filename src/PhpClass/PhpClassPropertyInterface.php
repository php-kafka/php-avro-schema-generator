<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

interface PhpClassPropertyInterface
{
    /**
     * @return string
     */
    public function getPropertyName(): string;

    /**
     * @return string
     */
    public function getPropertyType(): string;

    /**
     * @return mixed
     */
    public function getPropertyDefault();

    /**
     * @return ?string
     */
    public function getPropertyLogicalType(): ?string;

    /**
     * @return ?string
     */
    public function getPropertyDoc(): ?string;
}
