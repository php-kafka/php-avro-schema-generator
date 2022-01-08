<?php

namespace PhpKafka\PhpAvroSchemaGenerator\PhpClass;

/**
 * Interface to describe in arsing properties types which may be indeed very complex like:
 * \@var string[]|int[]|null|\DateInterval|DateInterval[]
 *
 * So, essentially array (union) of other (concrete) types which may be itself array or nots.
 */
interface PhpClassPropertyTypeInterface extends \JsonSerializable
{
    /**
     * If there is no unions, will return array with single value
     * @return PhpClassPropertyTypeItemInterface[]
     */
    public function getTypeItems(): array;
}
