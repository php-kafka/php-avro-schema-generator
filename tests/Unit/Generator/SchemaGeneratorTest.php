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

        $generator = new SchemaGenerator();
        $generator->setClassRegistry($registry);

        self::assertEquals($registry, $generator->getClassRegistry());
        self::assertEquals('/tmp', $generator->getOutputDirectory());
    }

    public function testGetters()
    {
        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $directory = '/tmp/foo';

        $generator = new SchemaGenerator();
        $generator->setClassRegistry($registry);
        $generator->setOutputDirectory($directory);

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
                        'type' => 'string',
                        'default' => 'test',
                        'doc' => 'test',
                        'logicalType' => 'test'
                    ]
                ]
            ]),
            'Test2Class' => json_encode([
                'type' => 'record',
                'name' => 'Test2Class',
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'default' => 'test',
                        'doc' => 'test',
                        'logicalType' => 'test'
                    ]
                ]
            ])
        ];

        $property1 = $this->getMockForAbstractClass(PhpClassPropertyInterface::class);
        $property1->expects(self::exactly(1))->method('getPropertyType')->willReturn(["type" => "array","items" => "test.foo"]);
        $property1->expects(self::exactly(1))->method('getPropertyName')->willReturn('items');
        $property1->expects(self::exactly(1))->method('getPropertyDefault')->willReturn(PhpClassPropertyInterface::NO_DEFAULT);

        $property2 = $this->getMockForAbstractClass(PhpClassPropertyInterface::class);
        $property2->expects(self::exactly(2))->method('getPropertyType')->willReturn('string');
        $property2->expects(self::exactly(2))->method('getPropertyName')->willReturn('name');
        $property2->expects(self::exactly(4))->method('getPropertyDefault')->willReturn('test');
        $property2->expects(self::exactly(6))->method('getPropertyDoc')->willReturn('test');
        $property2->expects(self::exactly(4))->method('getPropertyLogicalType')->willReturn('test');


        $class1 = $this->getMockForAbstractClass(PhpClassInterface::class);
        $class1->expects(self::once())->method('getClassName')->willReturn('TestClass');
        $class1->expects(self::exactly(2))->method('getClassNamespace')->willReturn('name\\space');
        $class1->expects(self::once())->method('getClassProperties')->willReturn([$property1, $property2]);

        $class2 = $this->getMockForAbstractClass(PhpClassInterface::class);
        $class2->expects(self::once())->method('getClassName')->willReturn('Test2Class');
        $class2->expects(self::once())->method('getClassNamespace')->willReturn(null);
        $class2->expects(self::once())->method('getClassProperties')->willReturn([$property2]);

        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $registry->expects(self::once())->method('getClasses')->willReturn([$class1, $class2]);

        $generator = new SchemaGenerator();
        $generator->setClassRegistry($registry);
        $result = $generator->generate();
        self::assertEquals($expectedResult, $result);
        self::assertCount(2, $result);
    }

    public function testGeneratePreservesZeroFraction()
    {
        $expectedResult = [
            'name.space.TestClass' => json_encode([
                'type' => 'record',
                'name' => 'TestClass',
                'namespace' => 'name.space',
                'fields' => [
                    [
                        'name' => 'name',
                        'type' => 'double',
                        'default' => 0.0,
                        'doc' => 'test',
                        'logicalType' => 'test'
                    ]
                ]
            ], JSON_PRESERVE_ZERO_FRACTION)
        ];

        $property = $this->getMockForAbstractClass(PhpClassPropertyInterface::class);
        $property->expects(self::exactly(1))->method('getPropertyType')->willReturn('double');
        $property->expects(self::exactly(1))->method('getPropertyName')->willReturn('name');
        $property->expects(self::exactly(2))->method('getPropertyDefault')->willReturn(0.0);
        $property->expects(self::exactly(3))->method('getPropertyDoc')->willReturn('test');
        $property->expects(self::exactly(2))->method('getPropertyLogicalType')->willReturn('test');


        $class = $this->getMockForAbstractClass(PhpClassInterface::class);
        $class->expects(self::once())->method('getClassName')->willReturn('TestClass');
        $class->expects(self::exactly(2))->method('getClassNamespace')->willReturn('name\\space');
        $class->expects(self::once())->method('getClassProperties')->willReturn([$property]);

        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $registry->expects(self::once())->method('getClasses')->willReturn([$class]);

        $generator = new SchemaGenerator();
        $generator->setClassRegistry($registry);
        $result = $generator->generate();
        self::assertEquals($expectedResult, $result);
        self::assertCount(1, $result);
    }

    public function testExportSchemas()
    {
        $schemas = [
            'filename' => 'test foo bar'
        ];

        $registry = $this->getMockForAbstractClass(ClassRegistryInterface::class);
        $generator = new SchemaGenerator();
        $generator->setClassRegistry($registry);
        $fileCount = $generator->exportSchemas($schemas);

        self::assertFileExists('/tmp/filename.avsc');
        self::assertEquals($schemas['filename'], file_get_contents('/tmp/filename.avsc'));
        self::assertEquals(1, $fileCount);

        unlink('/tmp/filename.avsc');
    }

    public function testGenerateWithoutRegistry()
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Please set a ClassRegistry for the generator');

        $generator = new SchemaGenerator();
        $refObject = new \ReflectionObject($generator);
        $refProperty = $refObject->getProperty('classRegistry');
        $refProperty->setAccessible( true );
        $refProperty->setValue($generator, null);
        $generator->generate();
    }
}
