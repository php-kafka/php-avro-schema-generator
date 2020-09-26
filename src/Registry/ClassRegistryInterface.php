<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Registry;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClass;

interface ClassRegistryInterface
{

    /**
     * @param  string $classDirectory
     * @return ClassRegistryInterface
     */
    public function addClassDirectory(string $classDirectory): ClassRegistryInterface;

    /**
     * @return array<string,int>
     */
    public function getClassDirectories(): array;

    /**
     * @return ClassRegistryInterface
     */
    public function load(): ClassRegistryInterface;

    /**
     * @return PhpClass[]
     */
    public function getClasses(): array;
}
