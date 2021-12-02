<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Merger;

use AvroSchema;
use PhpKafka\PhpAvroSchemaGenerator\Exception\SchemaMergerException;
use PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMerger;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Merger\SchemaMerger
 */
class SchemaMergerTest extends TestCase
{
    public function testGetSchemaRegistry()
    {
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $merger = new SchemaMerger($schemaRegistry);
        self::assertEquals($schemaRegistry, $merger->getSchemaRegistry());
    }

    public function testGetOutputDirectoryDefault()
    {
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $merger = new SchemaMerger($schemaRegistry);
        self::assertEquals('/tmp', $merger->getOutputDirectory());
    }

    public function testGetOutputDirectory()
    {
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $outputDirectory = '/root';
        $merger = new SchemaMerger($schemaRegistry, $outputDirectory);
        self::assertEquals($outputDirectory, $merger->getOutputDirectory());
    }

    public function testGetResolvedSchemaTemplateThrowsException()
    {
        self::expectException(\AvroSchemaParseException::class);

        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate->expects(self::once())->method('getSchemaDefinition')->willReturn('{"type": 1}');
        $merger = new SchemaMerger($schemaRegistry);

        self::assertEquals([], $merger->getResolvedSchemaTemplate($schemaTemplate));
    }

    public function testGetResolvedSchemaTemplateResolveEmbeddedException()
    {
        self::expectException(SchemaMergerException::class);
        self::expectExceptionMessage(sprintf(SchemaMergerException::UNKNOWN_SCHEMA_TYPE_EXCEPTION_MESSAGE, 'com.example.Page'));

        $definitionWithType = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": "com.example.Page" }, "default": [] }
            ]
        }';
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($definitionWithType);
        $merger = new SchemaMerger($schemaRegistry);

        self::assertEquals([], $merger->getResolvedSchemaTemplate($schemaTemplate));
    }

    public function testGetResolvedSchemaTemplate()
    {
        $rootDefinition = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": "com.example.Page" }, "default": [] }
            ]
        }';
        $subschemaDefinition = json_encode(
            json_decode(
                '{
                        "type": "record",
                        "namespace": "com.example",
                        "name": "Page",
                        "fields": [
                            { "name": "number", "type": "int" }
                        ]
                    }'
            )
        );

        $expectedResult = json_encode(
            json_decode(
                '{
                    "type": "record",
                    "name": "Book",
                    "namespace": "com.example",
                    "fields": [
                        { "name": "items", "type": {"type": "array", "items": {"type":"record","name":"Page","fields":[{"name":"number","type":"int"}]} }, "default": [] }
                    ]
                }'
            )
        );

        $subschemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $subschemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($subschemaDefinition);
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::once())
            ->method('getSchemaById')
            ->with('com.example.Page')
            ->willReturn($subschemaTemplate);
        $rootSchemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $rootSchemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($rootDefinition);
        $rootSchemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($rootSchemaTemplate);

        $merger = new SchemaMerger($schemaRegistry);

        $merger->getResolvedSchemaTemplate($rootSchemaTemplate);

        $parsedAvro = (string) (AvroSchema::parse($expectedResult));

        self::assertEquals($parsedAvro, json_encode(json_decode($expectedResult)));
    }

    public function testGetResolvedSchemaTemplateWithMultiEmbedd()
    {
        $rootDefinition = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": "com.example.Page" }, "default": [] },
                { "name": "defaultFont", "type": "com.example.Font" },
                { "name": "frontSide", "type": "com.example.other.Cover"},
                { "name": "backSide", "type": "com.example.other.Cover"}
            ]
        }';
        $subschemaDefinitionPage = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Page",
            "fields": [
                { "name": "number", "type": "int" },
                { "name": "font", "type": "com.example.Font" }
            ]
        }';


        $subschemaDefinitionFont = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Font",
            "fields": [
                { "name": "fontSize", "type": "int" },
                { "name": "fontType", "type": "string" }
            ]
        }';


        $subschemaDefinitionCover = '{
            "type": "record",
            "namespace": "com.example.other",
            "name": "Cover",
            "fields": [
                { "name": "title", "type": "string" },
                { "name": "image", "type": ["null", "com.example.other.cover_media"] }
            ]
        }';

        $subschemaDefinitionCoverMedia = '{
            "type": "record",
            "namespace": "com.example.other",
            "name": "cover_media",
            "fields": [
                { "name": "filePath", "type": "string" }
            ]
        }';

        $expectedResult = json_encode(
            json_decode(
                '{
                    "type": "record",
                    "name": "Book",
                    "namespace": "com.example",
                    "fields": [
                        { 
                            "name": "items",
                            "type": {
                                "type": "array",
                                "items": {
                                    "type":"record",
                                    "name":"Page",
                                    "fields":[
                                        {
                                            "name":"number",
                                            "type":"int"
                                        },
                                        {
                                            "name": "font",
                                            "type": {
                                                "type": "record",
                                                "name": "Font",
                                                "fields": [
                                                    { "name": "fontSize", "type": "int" },
                                                    { "name": "fontType", "type": "string" }
                                                ]
                                            }
                                        }
                                    ]
                                }
                            },
                            "default": []
                        },
                        {
                            "name": "defaultFont",
                            "type": "Font"
                        },
                        {
                            "name": "frontSide",
                            "type": {
                                "type": "record",
                                "name": "Cover",
                                "namespace": "com.example.other",
                                "fields": [
                                    { "name": "title", "type": "string" },
                                    { "name": "image", "type": [
                                           "null",
                                           {
                                                "type": "record",
                                                "name": "cover_media",
                                                "namespace": "com.example.other",
                                                "fields": [
                                                    { "name": "filePath", "type": "string" }
                                                ]
                                            }
                                       ]
                                    }
                                ]
                            }
                        },
                        { "name": "backSide", "type": "com.example.other.Cover"}
                    ]
                }'
            )
        );

        $subschemaTemplatePage = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $subschemaTemplatePage
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($subschemaDefinitionPage);
        $subschemaTemplateFont = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $subschemaTemplateFont
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($subschemaDefinitionFont);
        $subschemaTemplateCover = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $subschemaTemplateCover
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($subschemaDefinitionCover);
        $subschemaTemplateCoverMedia = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $subschemaTemplateCoverMedia
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($subschemaDefinitionCoverMedia);
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::exactly(4))
            ->method('getSchemaById')
            ->withConsecutive(
                ['com.example.Page'],
                ['com.example.Font'],
                ['com.example.other.Cover'],
                ['com.example.other.cover_media']
            )
            ->willReturnOnConsecutiveCalls(
                $subschemaTemplatePage,
                $subschemaTemplateFont,
                $subschemaTemplateCover,
                $subschemaTemplateCoverMedia
            );
        $schemaRegistry
            ->expects(self::once())
            ->method('getSchemaNamesPerNamespace')
            ->with('com.example')
            ->willReturn(['Font']);
        $rootSchemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $rootSchemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($rootDefinition);
        $rootSchemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($rootSchemaTemplate);

        $merger = new SchemaMerger($schemaRegistry);

        $merger->getResolvedSchemaTemplate($rootSchemaTemplate);
    }

    public function testGetResolvedSchemaTemplateWithDifferentNamespaceForEmbeddedSchema()
    {
        $rootDefinition = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": "com.example.other.Page" }, "default": [] }
            ]
        }';
        $subschemaDefinition = '{
            "type": "record",
            "namespace": "com.example.other",
            "name": "Page",
            "fields": [
                { "name": "number", "type": "int" }
            ]
        }';

        $expectedResult = json_encode(
            json_decode(
                '{
                    "type": "record",
                    "name": "Book",
                    "namespace": "com.example",
                    "fields": [
                        { 
                            "name": "items",
                            "type": {
                                "type": "array",
                                "items": {
                                    "type": "record",
                                    "name": "Page",
                                    "namespace": "com.example.other",
                                    "fields": [
                                        { "name": "number", "type": "int" }
                                    ]
                                }
                            },
                            "default": []
                        }
                    ]
                }'
            )
        );

        $subschemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $subschemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($subschemaDefinition);

        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::once())
            ->method('getSchemaById')
            ->with('com.example.other.Page')
            ->willReturn($subschemaTemplate);

        $rootSchemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $rootSchemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($rootDefinition);
        $rootSchemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($rootSchemaTemplate);

        $merger = new SchemaMerger($schemaRegistry);

        $merger->getResolvedSchemaTemplate($rootSchemaTemplate);
    }

    public function testMergeException()
    {
        self::expectException(SchemaMergerException::class);
        self::expectExceptionMessage(sprintf(SchemaMergerException::UNKNOWN_SCHEMA_TYPE_EXCEPTION_MESSAGE, 'com.example.Page'));

        $definitionWithType = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": ["string","com.example.Page"] }, "default": [] }
            ]
        }';
        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn($definitionWithType);

        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::once())
            ->method('getRootSchemas')
            ->willReturn([$schemaTemplate]);
        $merger = new SchemaMerger($schemaRegistry);
        $merger->merge();
    }

    public function testMerge()
    {
        $definition = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": ["string"] }, "default": [] }
            ]
        }';

        $expectedResult = json_encode(
            json_decode(
                '{
                    "type": "record",
                     "name": "Book",
                    "namespace": "com.example",
                    "fields": [
                        { "name": "items", "type": {"type": "array", "items": ["string"] }, "default": [] }
                    ]
                }'
            )
        );

        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::exactly(2))
            ->method('getSchemaDefinition')
            ->willReturn($definition);
        $schemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($schemaTemplate);

        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::once())
            ->method('getRootSchemas')
            ->willReturn([$schemaTemplate]);
        $merger = new SchemaMerger($schemaRegistry, '/tmp/foobar');
        $merger->merge(true);

        self::assertFileExists('/tmp/foobar/com.example.Book.avsc');
        unlink('/tmp/foobar/com.example.Book.avsc');
        rmdir('/tmp/foobar');
    }

    public function testMergePrimitive()
    {
        $definition = '{
            "type": "string"
        }';

        $expectedResult = json_encode(json_decode($definition));

        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::exactly(2))
            ->method('getSchemaDefinition')
            ->willReturn($definition);
        $schemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($schemaTemplate);
        $schemaTemplate
            ->expects(self::once())
            ->method('getFilename')
            ->willReturn('primitive-type.avsc');

        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::once())
            ->method('getRootSchemas')
            ->willReturn([$schemaTemplate]);
        $merger = new SchemaMerger($schemaRegistry, '/tmp/foobar');
        $merger->merge(false, true);

        self::assertFileExists('/tmp/foobar/primitive-type.avsc');
        unlink('/tmp/foobar/primitive-type.avsc');
        rmdir('/tmp/foobar');
    }

    public function testMergeWithFilenameOption()
    {
        $definition = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { "name": "items", "type": {"type": "array", "items": ["string"] }, "default": [] }
            ]
        }';

        $expectedResult = json_encode(
            json_decode(
                '{
                    "type": "record",
                    "name": "Book",
                    "namespace": "com.example",
                    "fields": [
                        { "name": "items", "type": {"type": "array", "items": ["string"] }, "default": [] }
                    ]
                }'
            )
        );

        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::exactly(2))
            ->method('getSchemaDefinition')
            ->willReturn($definition);
        $schemaTemplate
            ->expects(self::once())
            ->method('withSchemaDefinition')
            ->with($expectedResult)
            ->willReturn($schemaTemplate);
        $schemaTemplate
            ->expects(self::once())
            ->method('getFilename')
            ->willReturn('bla.avsc');

        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);
        $schemaRegistry
            ->expects(self::once())
            ->method('getRootSchemas')
            ->willReturn([$schemaTemplate]);
        $merger = new SchemaMerger($schemaRegistry, '/tmp/foobar');
        $merger->merge(true, true);

        self::assertFileExists('/tmp/foobar/bla.avsc');
        unlink('/tmp/foobar/bla.avsc');
        rmdir('/tmp/foobar');
    }

    public function testExportSchema()
    {
        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn('{"name": "test"}');
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);

        $merger = new SchemaMerger($schemaRegistry);
        $merger->exportSchema($schemaTemplate);

        self::assertFileExists('/tmp/test.avsc');
        unlink('/tmp/test.avsc');
    }

    public function testExportSchemaPrimitiveWithWrongOptions()
    {
        $schemaTemplate = $this->getMockForAbstractClass(SchemaTemplateInterface::class);
        $schemaTemplate
            ->expects(self::once())
            ->method('getSchemaDefinition')
            ->willReturn('{"type": "string"}');
        $schemaTemplate
            ->expects(self::exactly(2))
            ->method('isPrimitive')
            ->willReturn(true);

        $schemaTemplate
            ->expects(self::once())
            ->method('getFilename')
            ->willReturn('test.avsc');
        $schemaRegistry = $this->getMockForAbstractClass(SchemaRegistryInterface::class);

        $merger = new SchemaMerger($schemaRegistry);
        $merger->exportSchema($schemaTemplate, true);

        self::assertFileExists('/tmp/test.avsc');
        unlink('/tmp/test.avsc');
    }
}
