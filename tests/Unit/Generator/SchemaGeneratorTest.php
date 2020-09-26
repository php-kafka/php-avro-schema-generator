<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Generator;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator
 */
class SchemaGeneratorTest extends TestCase
{
    public function testDefaultOutputDirectory()
    {
        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);

        $generator = new SchemaGenerator($registry);

        self::assertEquals($registry, $generator->getClassRegistry());
        self::assertEquals('/tmp', $generator->getOutputDirectory());
    }

    public function testGetters()
    {
        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $directory = '/tmp/foo';

        $generator = new SchemaGenerator($registry, $directory);

        self::assertEquals($registry, $generator->getClassRegistry());
        self::assertEquals($directory, $generator->getOutputDirectory());
    }

    public function testGenerate()
    {
        $expectedResult = [
            'name.space.TestClass' => json_encode([
                'type' => 'record',
                'name' => 'TestClass',
                'namespace' => 'name.space',
                'fields' => [
                    [
                        'name' => 'items',
                        'type' => [
                            'type' => 'array',
                            'items' => 'test.foo'
                        ]
                    ],
                    [
                        'name' => 'name',
                        'type' => 'string'
                    ]
                ]
            ]),
            'name.space.Test2Class' => json_encode([
                'type' => 'record',
                'name' => 'Test2Class',
                'namespace' => 'name.space',
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'string'
                    ]
                ]
            ])
        ];

        $property1 = $this->getMockForAbstractClass(PhpClassPropertyInterface::class);
        $property1->expects(self::exactly(3))->method('getPropertyType')->willReturn('array');
        $property1->expects(self::exactly(1))->method('getPropertyName')->willReturn('items');
        $property1->expects(self::exactly(1))->method('getPropertyArrayType')->willReturn('test\\foo');

        $property2 = $this->getMockForAbstractClass(PhpClassPropertyInterface::class);
        $property2->expects(self::exactly(6))->method('getPropertyType')->willReturn('string');
        $property2->expects(self::exactly(2))->method('getPropertyName')->willReturn('name');

        $property3 = $this->getMockForAbstractClass(PhpClassPropertyInterface::class);
        $property3->expects(self::once())->method('getPropertyType')->willReturn('mixed');
        $property3->expects(self::never())->method('getPropertyName');

        $class1 = $this->getMockForAbstractClass(PhpClassInterface::class);
        $class1->expects(self::once())->method('getClassName')->willReturn('TestClass');
        $class1->expects(self::once())->method('getClassNamespace')->willReturn('name\\space');
        $class1->expects(self::once())->method('getClassProperties')->willReturn([$property1, $property2, $property3]);

        $class2 = $this->getMockForAbstractClass(PhpClassInterface::class);
        $class2->expects(self::once())->method('getClassName')->willReturn('Test2Class');
        $class2->expects(self::once())->method('getClassNamespace')->willReturn('name\\space');
        $class2->expects(self::once())->method('getClassProperties')->willReturn([$property2]);

        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $registry->expects(self::once())->method('getClasses')->willReturn([$class1, $class2]);

        $generator = new SchemaGenerator($registry);
        $result = $generator->generate();
        self::assertEquals($expectedResult, $result);
        self::assertCount(2, $result);
    }

    public function testExportSchemas()
    {
        $schemas = [
            'filename' => 'test foo bar'
        ];

        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $generator = new SchemaGenerator($registry);
        $fileCount = $generator->exportSchemas($schemas);

        self::assertFileExists('/tmp/filename.avsc');
        self::assertEquals($schemas['filename'], file_get_contents('/tmp/filename.avsc'));
        self::assertEquals(1, $fileCount);

        unlink('/tmp/filename.avsc');
    }
}
