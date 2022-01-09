<?php

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

class PhpClassPropertyType implements PhpClassPropertyTypeInterface
{
    /**
     * @var PhpClassPropertyTypeItemInterface[]
     */
    private array $types;

    /**
     * @param PhpClassPropertyTypeItemInterface[] $types
     */
    public function __construct(PhpClassPropertyTypeItemInterface ...$types)
    {
        $this->types = $types;
    }

    /**
     * @inheritdoc
     */
    public function getTypeItems(): array
    {
        return $this->types;
    }

    public function isNullable(): bool
    {
        return (bool)current(array_filter($this->types, function ($type) { return !$type->isArray() && 'null' == $type->getItemType(); }));
    }

    /**
     * Allow easy serialization into JSON
     * @return array|mixed|PhpClassPropertyTypeItemInterface|PhpClassPropertyTypeItemInterface[]
     */
    public function jsonSerialize()
    {
        if (0 == count($this->types)){
            return [];
        }
        if (1 == count($this->types)){
            return $this->types[0];
        }
        return $this->types;
    }
}
