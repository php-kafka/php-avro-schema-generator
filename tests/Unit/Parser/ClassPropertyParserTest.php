<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\UnionType;
use PhpParser\Node\VarLikeIdentifier;
use PHPUnit\Framework\TestCase;

class ClassPropertyParserTest extends TestCase
{
    public function testParseProperty(): void
    {
        $classParser = $this->getMockBuilder(ClassParser::class)->disableOriginalConstructor()->getMock();
        $doc = $this->getMockBuilder(Doc::class)->disableOriginalConstructor()->getMock();
        $varId = $this->getMockBuilder(VarLikeIdentifier::class)->disableOriginalConstructor()->getMock();
        $varId->name = 'bla';
        $identifier = $this->getMockBuilder(Identifier::class)->disableOriginalConstructor()->getMock();
        $identifier->name = 'int';
        $ut = $this->getMockBuilder(UnionType::class)->disableOriginalConstructor()->getMock();
        $ut->types = [$identifier];
        $propertyProperty = $this->getMockBuilder(PropertyProperty::class)->disableOriginalConstructor()->getMock();
        $propertyProperty->name = $varId;
        $nullableType = $this->getMockBuilder(NullableType::class)->disableOriginalConstructor()->getMock();
        $nullableType->type = $identifier;
        $doc->expects(self::once())->method('getText')->willReturn('bla');
        $docParser = $this->getMockForAbstractClass(DocCommentParserInterface::class);
        $property1 = $this->getMockBuilder(Property::class)->disableOriginalConstructor()->getMock();
        $property1->expects(self::once())->method('getAttributes')->willReturn(['comments' => [$doc]]);
        $property1->props = [$propertyProperty];
        $property1->type = $identifier;
        $property2 = $this->getMockBuilder(Property::class)->disableOriginalConstructor()->getMock();
        $property2->type = 'string';
        $property2->props = [$propertyProperty];
        $property3 = $this->getMockBuilder(Property::class)->disableOriginalConstructor()->getMock();
        $property3->type = $ut;
        $property3->props = [$propertyProperty];
        $property4 = $this->getMockBuilder(Property::class)->disableOriginalConstructor()->getMock();
        $property4->type = $nullableType;
        $property4->props = [$propertyProperty];
        $cpp = new ClassPropertyParser($docParser);

        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property1, $classParser));
        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property2, $classParser));
        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property3, $classParser));
        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property4, $classParser));
    }
}
