<?php

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

/**
 * Interface to describe in arsing properties types which may be indeed very complex like:
 * \@var string[]|int[]|null|\DateInterval|DateInterval[]|\App\Some\Path\MyClass
 *
 * That interface represent single part in that union.
 */
interface PhpClassPropertyTypeItemInterface extends \JsonSerializable
{
    public function isArray(): bool;
    public function getItemType(): string;
}
