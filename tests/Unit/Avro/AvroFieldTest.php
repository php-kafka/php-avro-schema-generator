<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Avro;

use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroField;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Avro\AvroField
 */
class AvroFieldTest extends TestCase
{
    public function testGetters()
    {
        $field = new AvroField('propertyName', 'array', 'default', 'doc', 'logicalType');

        self::assertEquals('propertyName', $field->getFieldName());
        self::assertEquals('array', $field->getFieldType());
        self::assertEquals('default', $field->getFieldDefault());
        self::assertEquals('doc', $field->getFieldDoc());
        self::assertEquals('logicalType', $field->getFieldLogicalType());
    }
}
