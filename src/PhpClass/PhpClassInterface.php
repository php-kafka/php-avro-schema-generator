<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

interface PhpClassInterface
{
    public function getClassNamespace(): string;

    public function getClassName(): string;

    public function getClassBody(): string;

    /**
     * @return PhpClassProperty[]
     */
    public function getClassProperties(): array;
}
