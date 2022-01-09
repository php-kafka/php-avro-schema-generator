<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Avro;

final class AvroRecord implements AvroRecordInterface
{
    private string $recordName;
    private ?string $recordNamespace;

    /**
     * @var AvroFieldInterface[]
     */
    private array $recordFields;

    /**
     * @param string $recordName
     * @param ?string $recordNamespace
     * @param AvroFieldInterface[]  $recordFields
     */
    public function __construct(string $recordName, ?string $recordNamespace, array $recordFields)
    {
        $this->recordName = $recordName;
        $this->recordNamespace = $recordNamespace;
        $this->recordFields = $recordFields;
    }

    /**
     * @return string
     */
    public function getRecordNamespace(): ?string
    {
        return $this->recordNamespace;
    }

    /**
     * @return string
     */
    public function getRecordName(): string
    {
        return $this->recordName;
    }

    /**
     * @return AvroFieldInterface[]
     */
    public function getRecordFields(): array
    {
        return $this->recordFields;
    }
}
