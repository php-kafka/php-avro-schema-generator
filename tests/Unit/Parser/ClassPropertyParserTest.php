<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParserInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\UnionType;
use PhpParser\Node\VarLikeIdentifier;
use PHPUnit\Framework\TestCase;

class ClassPropertyParserTest extends TestCase
{
    public function testParseProperty(): void
    {
        $doc = $this->getMockBuilder(Doc::class)->disableOriginalConstructor()->getMock();
        $varId = $this->getMockBuilder(VarLikeIdentifier::class)->disableOriginalConstructor()->getMock();
        $varId->name = 'bla';
        $identifier = $this->getMockBuilder(Identifier::class)->disableOriginalConstructor()->getMock();
        $identifier->name = 'int';
        $ut = $this->getMockBuilder(UnionType::class)->disableOriginalConstructor()->getMock();
        $ut->types = [$identifier];
        $propertyProperty = $this->getMockBuilder(PropertyProperty::class)->disableOriginalConstructor()->getMock();
        $propertyProperty->name = $varId;
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
        $cpp = new ClassPropertyParser($docParser);

        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property1));
        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property2));
        self::assertInstanceOf(PhpClassPropertyInterface::class, $cpp->parseProperty($property3));
    }

    public function testParsePropertyExceptionOnNonProperty(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Property must be of type: PhpParser\Node\Stmt\Property');
        $docParser = $this->getMockForAbstractClass(DocCommentParserInterface::class);
        $cpp = new ClassPropertyParser($docParser);

        $cpp->parseProperty(1);
    }
}