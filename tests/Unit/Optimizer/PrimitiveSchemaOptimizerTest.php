<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\PrimitiveSchemaOptimizer;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;
use PHPUnit\Framework\TestCase;

class PrimitiveSchemaOptimizerTest extends TestCase
{
    public function testOptimize(): void
    {
        $schema = '{"type": "string"}';

        $expectedResult = json_encode(json_decode('"string"'));


        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($schema);

        $schemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($schemaTemplate);

        $schemaTemplate
            ->expects(self::once())
            ->method('isPrimitive')
            ->willReturn(true);

        $optimizer = new PrimitiveSchemaOptimizer();

        self::assertInstanceOf(SchemaTemplateInterface::class, $optimizer->optimize($schemaTemplate));
    }

    public function testOptimizeForStringSchema(): void
    {
        $schema = '"string"';

        $expectedResult = json_encode(json_decode('"string"'));


        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($schema);

        $schemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($schemaTemplate);

        $schemaTemplate
            ->expects(self::once())
            ->method('isPrimitive')
            ->willReturn(true);

        $optimizer = new PrimitiveSchemaOptimizer();

        self::assertInstanceOf(SchemaTemplateInterface::class, $optimizer->optimize($schemaTemplate));
    }

    public function testOptimizeForRecordSchema(): void
    {
        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate->expects(self::never())->method('getSchemaDefinition');
        $schemaTemplate->expects(self::never())->method('withSchemaDefinition');

        $schemaTemplate
            ->expects(self::once())
            ->method('isPrimitive')
            ->willReturn(false);

        $optimizer = new PrimitiveSchemaOptimizer();

        self::assertInstanceOf(SchemaTemplateInterface::class, $optimizer->optimize($schemaTemplate));
    }
}