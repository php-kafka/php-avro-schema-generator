<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\PrimitiveSchemaOptimizer;
use PHPUnit\Framework\TestCase;

class PrimitiveSchemaOptimizerTest extends TestCase
{
    public function testOptimize(): void
    {
        $schema = '{"type": "string"}';

        $expectedResult = json_encode(json_decode('"string"'));

        $optimizer = new PrimitiveSchemaOptimizer();

        self::assertEquals($expectedResult, $optimizer->optimize($schema, true));
    }

    public function testOptimizeForStringSchema(): void
    {
        $schema = '"string"';

        $expectedResult = json_encode(json_decode('"string"'));

        $optimizer = new PrimitiveSchemaOptimizer();

        self::assertEquals($expectedResult, $optimizer->optimize($schema, true));
    }

    public function testOptimizeForRecordSchema(): void
    {
        $schema = '{"type":"record","namespace":"com.example","name":"Book","fields":[{"name":"isbn","type":"string"}]}';

        $expectedResult = json_encode(json_decode($schema));

        $optimizer = new PrimitiveSchemaOptimizer();

        self::assertEquals($expectedResult, $optimizer->optimize($schema, false));
    }
}