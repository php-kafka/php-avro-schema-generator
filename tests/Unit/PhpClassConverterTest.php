<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverter;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroRecordInterface;
use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroFieldInterface;
use PHPUnit\Framework\TestCase;

class PhpClassConverterTest extends TestCase
{
    public function testConvert(): void
    {
        $property1 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property1->expects(self::once())->method('getFieldType')->willReturn(1);
        $property2 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property2->expects(self::exactly(2))->method('getFieldType')->willReturn('string|array|int[]|mixed[]');
        $property3 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property3->expects(self::exactly(2))->method('getFieldType')->willReturn('string');
        $property4 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property4->expects(self::exactly(2))->method('getFieldType')->willReturn('object|XYZ|UC');
        $property5 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property5->expects(self::exactly(2))->method('getFieldType')->willReturn('mixed');
        $property6 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property6->expects(self::exactly(2))->method('getFieldType')->willReturn('array|mixed[]');


        $parser = $this->getMockForAbstractClass(ClassParserInterface::class);
        $parser->expects(self::once())->method('setCode')->with('some class stuff');
        $parser->expects(self::exactly(2))->method('getClassName')->willReturn('foo');
        $parser->expects(self::once())->method('getProperties')->willReturn(
            [$property1, $property2, $property3, $property4, $property5, $property6]
        );
        $parser->expects(self::exactly(2))->method('getUsedClasses')->willReturn(['XYZ' => 'a\\b\\ZYX']);
        $parser->expects(self::exactly(3))->method('getNamespace')->willReturn('x\\y');

        $converter = new PhpClassConverter($parser);
        self::assertInstanceOf(AvroRecordInterface::class, $converter->convert('some class stuff'));
    }

    public function testConvertWithNoNamesace(): void
    {
        $property1 = $this->getMockForAbstractClass(AvroFieldInterface::class);
        $property1->expects(self::exactly(2))->method('getFieldType')->willReturn('ABC');


        $parser = $this->getMockForAbstractClass(ClassParserInterface::class);
        $parser->expects(self::once())->method('setCode')->with('some class stuff');
        $parser->expects(self::exactly(2))->method('getClassName')->willReturn('foo');
        $parser->expects(self::once())->method('getProperties')->willReturn([$property1]);
        $parser->expects(self::exactly(1))->method('getUsedClasses')->willReturn([]);
        $parser->expects(self::exactly(2))->method('getNamespace')->willReturn(null);

        $converter = new PhpClassConverter($parser);
        self::assertInstanceOf(AvroRecordInterface::class, $converter->convert('some class stuff'));
    }

    public function testConvertOfNonClass(): void
    {
        $parser = $this->getMockForAbstractClass(ClassParserInterface::class);
        $parser->expects(self::once())->method('getClassName')->willReturn(null);
        $converter = new PhpClassConverter($parser);
        self::assertNull($converter->convert('some class stuff'));
    }
}