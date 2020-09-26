<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Schema;

use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplate;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplate
 */
class SchemaTemplateTest extends TestCase
{
    public function testSchemaId()
    {
        $template = (new SchemaTemplate())->withSchemaId('test');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('test', $template->getSchemaId());
    }

    public function testSchemaLevel()
    {
        $template = (new SchemaTemplate())->withSchemaLevel('root');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('root', $template->getSchemaLevel());
    }

    public function testSchemaDefinition()
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('test');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('test', $template->getSchemaDefinition());
    }

    public function testFilename()
    {
        $template = (new SchemaTemplate())->withFilename('test');

        self::assertInstanceOf(SchemaTemplateInterface::class, $template);
        self::assertEquals('test', $template->getFilename());
    }

    public function testIsPrimitiveTrue()
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('{"type":"string"}');

        self::assertTrue($template->isPrimitive($template));
    }

    public function testIsPrimitiveFalse()
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('{"type":"record"}');

        self::assertFalse($template->isPrimitive($template));
    }

    public function testIsPrimitiveFalseOnMissingType()
    {
        $template = (new SchemaTemplate())->withSchemaDefinition('{"foo":"bar"}');

        self::assertFalse($template->isPrimitive($template));
    }
}
