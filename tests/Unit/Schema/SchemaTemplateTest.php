<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Schema;

use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplate;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplate
 */
class SchemaTemplateTest extends TestCase
{
    public function testSchemaId(): void
    {
        $template = (new SchemaTemplate())->withSchemaId('test');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('test', $template->getSchemaId());
    }

    public function testSchemaLevel(): void
    {
        $template = (new SchemaTemplate())->withSchemaLevel('root');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('root', $template->getSchemaLevel());
    }

    public function testSchemaDefinition(): void
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('test');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('test', $template->getSchemaDefinition());
    }

    public function testFilename(): void
    {
        $template = (new SchemaTemplate())->withFilename('test');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('test', $template->getFilename());
    }

    public function testIsPrimitiveTrue(): void
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('{"type":"string"}');

        self::assertTrue($template->isPrimitive());
    }

    public function testIsPrimitiveFalse(): void
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('{"type":"record"}');

        self::assertFalse($template->isPrimitive());
    }

    public function testIsPrimitiveTrueForOptimizedSchema(): void
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('"string"');

        self::assertTrue($template->isPrimitive());
    }

    public function testIsPrimitiveFalseForOptimizedSchema(): void
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('"foo"');

        self::assertFalse($template->isPrimitive());
    }

    public function testIsPrimitiveFalseOnMissingType(): void
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('{"foo":"bar"}');

        self::assertFalse($template->isPrimitive());
    }
}
