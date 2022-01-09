<?php

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

class PhpClassPropertyTypeItem implements PhpClassPropertyTypeItemInterface
{
    private string $itemType;
    private bool $isArray;

    public function __construct(string $itemType, bool $isArray = false)
    {
        $this->isArray = $isArray;
        $this->itemType = $itemType;
    }

    public function isArray(): bool
    {
        return $this->isArray;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function jsonSerialize(): mixed
    {
        if ($this->isArray()){
            return [
                'type' => 'array',
                'items' => $this->getItemType()
            ];
        }
        else {
            return $this->getItemType();
        }
    }
}
