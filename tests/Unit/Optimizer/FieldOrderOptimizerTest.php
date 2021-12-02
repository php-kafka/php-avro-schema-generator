<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FieldOrderOptimizer;
use PHPUnit\Framework\TestCase;

class FieldOrderOptimizerTest extends TestCase
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
                    "items": ["null", "com.example.Page"]
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
                { "name": "backSide", "type": "com.example.other.Cover"},
                                {
                    "name": "authors",
                    "type": "array",
                    "items": [
                        "null",
                        {
                            "type": "record",
                            "namespace": "com.example.other",
                            "name": "author",
                            "fields": [
                                { "name": "name", "type": "string" },
                                {
                                    "name": "contact",
                                    "type": {
                                        "name": "contact",
                                        "type": "record",
                                        "fields": [
                                            { "name": "address", "type": "string" }
                                        ]
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        }';

        $expectedResult = json_encode(json_decode('{
            "type": "record",
            "name": "Book",
            "namespace": "com.example",
            "fields": [
                { 
                    "name": "content",
                    "type": {
                        "type": "array",
                        "items": {
                            "type":"record",
                            "name":"Page",
                            "namespace": "com.example",
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
                                        "namespace": "com.example",
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
                    "items": ["null", "com.example.Page"]
                },
                {
                    "name": "defaultFont",
                    "type": "com.example.Font"
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
                { "name": "backSide", "type": "com.example.other.Cover"},
                                {
                    "name": "authors",
                    "type": "array",
                    "items": [
                        "null",
                        {
                            "type": "record",
                            "name": "author",
                            "namespace": "com.example.other",
                            "fields": [
                                { "name": "name", "type": "string" },
                                {
                                    "name": "contact",
                                    "type": {
                                        "type": "record",
                                        "name": "contact",
                                        "fields": [
                                            { "name": "address", "type": "string" }
                                        ]
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        }'));

        $optimizer = new FieldOrderOptimizer();

        self::assertEquals($expectedResult, $optimizer->optimize($schema));
    }
}