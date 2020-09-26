<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Exception;

class SchemaRegistryException extends \Exception
{
    public const FILE_PATH_EXCEPTION_MESSAGE = 'Unable to get file path';
    public const FILE_NOT_READABLE_EXCEPTION_MESSAGE = 'Unable to read file: %s';
}
