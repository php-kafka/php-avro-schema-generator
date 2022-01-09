<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroField;

interface ClassParserInterface
{
    public function getClassName(): ?string;

    public function getNamespace(): ?string;

    /**
     * @return AvroField[]
     */
    public function getProperties(): array;

    /**
     * @return array<string, string>
     */
    public function getUsedClasses(): array;

    public function getParentClassName(): ?string;

    public function setCode(string $code): void;
}
