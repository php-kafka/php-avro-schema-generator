<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;

interface ClassParserInterface
{
    public function getClassName(): ?string;

    public function getNamespace(): ?string;

    /**
     * @return PhpClassProperty[]
     */
    public function getProperties(): array;

    /**
     * @return array<string, string>
     */
    public function getUsedClasses(): array;
}