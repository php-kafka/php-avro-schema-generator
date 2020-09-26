<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Exception;

class SchemaMergerException extends \Exception
{
    public const NO_SCHEMA_REGISTRY_SET_EXCEPTION_MESSAGE = 'No schema registry set.';
    public const UNKNOWN_SCHEMA_TYPE_EXCEPTION_MESSAGE = 'Unknown schema type: %s';
    public const UNABLE_TO_FIND_SUBSCHEMA = 'Was unable to replace subschema %s in definition %s';
}
