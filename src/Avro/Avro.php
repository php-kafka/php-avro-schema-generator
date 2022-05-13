<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Avro;

class Avro
{
    public const FILE_EXTENSION = 'avsc';
    public const LONELIEST_NUMBER = 1;
    public const BASIC_TYPES = [
        'null' => self::LONELIEST_NUMBER,
        'boolean' => self::LONELIEST_NUMBER,
        'int' => self::LONELIEST_NUMBER,
        'long' => self::LONELIEST_NUMBER,
        'float' => self::LONELIEST_NUMBER,
        'double' => self::LONELIEST_NUMBER,
        'bytes' => self::LONELIEST_NUMBER,
        'string' => self::LONELIEST_NUMBER,
        'enum' => self::LONELIEST_NUMBER,
        'array' => self::LONELIEST_NUMBER,
        'map' => self::LONELIEST_NUMBER,
        'fixed' => self::LONELIEST_NUMBER,
    ];

    /**
     * @var string[]
     */
    public const  MAPPED_TYPES = array(
        'null' => 'null',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'string' => 'string',
        'int' => 'int',
        'integer' => 'int',
        'float' => 'float',
        'double' => 'double',
        'array' => 'array',
        'object' => 'object',
        'callable' => 'callable',
        'resource' => 'resource',
        'mixed' => 'mixed',
        'Collection' => 'array',
    );
}
