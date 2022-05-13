<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

final class PhpClass implements PhpClassInterface
{
    private string $classBody;
    private string $className;
    private ?string $classNamespace;

    /**
     * @var PhpClassPropertyInterface[]
     */
    private array $classProperties;

    /**
     * @param string $className
     * @param ?string $classNamespace
     * @param string $classBody
     * @param PhpClassPropertyInterface[]  $classProperties
     */
    public function __construct(string $className, ?string $classNamespace, string $classBody, array $classProperties)
    {
        $this->className = $className;
        $this->classNamespace = $classNamespace;
        $this->classBody = $classBody;
        $this->classProperties = $classProperties;
    }

    /**
     * @return string
     */
    public function getClassNamespace(): ?string
    {
        return $this->classNamespace;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getClassBody(): string
    {
        return $this->classBody;
    }

    /**
     * @return PhpClassPropertyInterface[]
     */
    public function getClassProperties(): array
    {
        return $this->classProperties;
    }
}
