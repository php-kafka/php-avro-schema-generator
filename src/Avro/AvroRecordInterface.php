<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Avro;

interface AvroRecordInterface
{
    public function getRecordNamespace(): ?string;

    public function getRecordName(): string;

    /**
     * @return AvroFieldInterface[]
     */
    public function getRecordFields(): array;
}
