<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FullNameOptimizer;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;
use PHPUnit\Framework\TestCase;

class FullNameOptimizerTest extends TestCase
{
    public function testOptimize(): void
    {
        $schema = '{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { 
                    "name": "content",
                    "type": {
                        "type": "array",
                        "items": {
                            "type":"record",
                            "namespace": "com.example",
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
                                        "namespace": "com.example",
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
                    "name": "foreword",
                    "type": "array",
                    "items": ["null","com.example.Page"]
                },
                {
                    "name": "appendix",
                    "type": "array",
                    "items": "com.example.Page"
                },
                {
                    "name": "defaultFont",
                    "type": "com.example.Font"
                },
                {
                    "name": "frontSide",
                    "type": {
                        "type": "record",
                        "namespace": "com.example.other",
                        "name": "Cover",
                        "fields": [
                            { "name": "title", "type": "string" },
                            { "name": "image", "type": [
                                   "null",
                                   {
                                        "type": "record",
                                        "namespace": "com.example.other",
                                        "name": "cover_media",
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
        }';

        $expectedResult = json_encode(json_decode('{
            "type": "record",
            "namespace": "com.example",
            "name": "Book",
            "fields": [
                { 
                    "name": "content",
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
                    "name": "foreword",
                    "type": "array",
                    "items": ["null","Page"]
                },
                {
                    "name": "appendix",
                    "type": "array",
                    "items": "Page"
                },
                {
                    "name": "defaultFont",
                    "type": "Font"
                },
                {
                    "name": "frontSide",
                    "type": {
                        "type": "record",
                        "namespace": "com.example.other",
                        "name": "Cover",
                        "fields": [
                            { "name": "title", "type": "string" },
                            { "name": "image", "type": [
                                   "null",
                                   {
                                        "type": "record",
                                        "name": "cover_media",
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
        }'));


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

        $optimizer = new FullNameOptimizer();

        self::assertInstanceOf(SchemaTemplateInterface::class, $optimizer->optimize($schemaTemplate));
    }
}