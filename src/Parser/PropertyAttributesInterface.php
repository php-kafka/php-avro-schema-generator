<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

interface PropertyAttributesInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getTypes(): string;

    /**
     * @return null|mixed
     */
    public function getDefault();

    /**
     * @return string
     */
    public function getLogicalType(): ?string;

    /**
     * @return string
     */
    public function getDoc(): ?string;
}