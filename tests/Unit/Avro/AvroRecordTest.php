<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Avro;

use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Avro\AvroRecord
 */
class AvroRecordTest extends TestCase
{
    public function testGetters()
    {
        $avroRecord = new AvroRecord('TestClass', 'Test\\Space', []);

        self::assertEquals('TestClass', $avroRecord->getRecordName());
        self::assertEquals('Test\\Space', $avroRecord->getRecordNamespace());
        self::assertEquals([], $avroRecord->getRecordFields());
    }
}
