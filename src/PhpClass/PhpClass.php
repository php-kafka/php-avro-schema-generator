<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

class PhpClass implements PhpClassInterface
{
    /**
     * @var string
     */
    private $classBody;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $classNamespace;

    /**
     * @var PhpClassProperty[]
     */
    private $classProperties;

    /**
     * @param string $className
     * @param string $classNamespace
     * @param string $classBody
     * @param PhpClassProperty[]  $classProperties
     */
    public function __construct(string $className, string $classNamespace, string $classBody, array $classProperties)
    {
        $this->className = $className;
        $this->classNamespace = $classNamespace;
        $this->classBody = $classBody;
        $this->classProperties = $classProperties;
    }

    /**
     * @return string
     */
    public function getClassNamespace(): string
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
     * @return PhpClassProperty[]
     */
    public function getClassProperties(): array
    {
        return $this->classProperties;
    }
}
