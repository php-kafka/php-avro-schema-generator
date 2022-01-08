<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyType;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyTypeInterface;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyTypeItem;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;

class ClassPropertyParser implements ClassPropertyParserInterface
{
    private DocCommentParserInterface $docParser;

    /**
     * @param DocCommentParserInterface $docParser
     */
    public function __construct(DocCommentParserInterface $docParser)
    {
        $this->docParser = $docParser;
    }

    /**
     * @inheritdoc
     */
    public function parseProperty(Property $property, ClassParserInterface $classParser): PhpClassPropertyInterface
    {
        $propertyAttributes = $this->getPropertyAttributes($property, $classParser);

        return new PhpClassProperty(
            $propertyAttributes['name'],
            $propertyAttributes['types'],
            $propertyAttributes['default'],
            $propertyAttributes['doc'],
            $propertyAttributes['logicalType']
        );
    }

    /**
     * @param Property $property
     * @return array<string, mixed>
     */
    protected function getPropertyAttributes(Property $property): array
    {
        $attributes = $this->getEmptyAttributesArray();
        $docComments = $this->getAllPropertyDocComments($property);
        $attributes['name'] = $this->getPropertyName($property);

        $attributes['types'] = $this->getTypeFromDocComment($docComments);
        if (null === $attributes['types']) {
            $attributes['types'] = $this->getPropertyType($property, $docComments);
        }

        $attributes['default'] = $this->getDefaultFromDocComment($docComments);
        $attributes['doc'] = $this->getDocFromDocComment($docComments);
        $attributes['logicalType'] = $this->getLogicalTypeFromDocComment($docComments);

        return $attributes;
    }

    private function getPropertyName(Property $property): string
    {
        return $property->props[0]->name->name;
    }

    /**
     * @param Property $property
     * @param array<string,mixed> $docComments
     * @return PhpClassPropertyTypeInterface
     */
    private function getPropertyType(Property $property, array $docComments): PhpClassPropertyTypeInterface
    {
        if ($property->type instanceof NullableType) {
            if ($property->type->type instanceof Identifier) {
                return new PhpClassPropertyType(new PhpClassPropertyTypeItem('null'), $this->mapPropertyTypeItem($property->type->type->name));
            }
        } elseif ($property->type instanceof Identifier) {
            return new PhpClassPropertyType($this->mapPropertyTypeItem($property->type->name));
        } elseif ($property->type instanceof UnionType) {
            return new PhpClassPropertyType(
                ...array_map(
                    function($type){
                        return new PhpClassPropertyTypeItem($type->name);
                    },
                    $property->type->types)
            );
        }

        return $this->getDocCommentByType($docComments, 'var');
    }

    /**
     * @param string $typeName values like 'string', or 'string|int',  or 'string|int[]'
     * @return PhpClassPropertyType
     */
    protected function mapPropertyType(string $typeName): PhpClassPropertyType
    {
        return new PhpClassPropertyType(
            ...array_map([$this, 'mapPropertyTypeItem'], explode('|', $typeName))
        );
    }

    /**
     * @param string $typeName Handle single type item like: 'string', 'string[]'
     * @return PhpClassPropertyTypeItem
     */
    protected function mapPropertyTypeItem(string $typeName): PhpClassPropertyTypeItem
    {
        $arr = explode('[]', $typeName);
        $itemTypeName = $arr[0];

        return new PhpClassPropertyTypeItem(Avro::MAPPED_TYPES[$itemTypeName] ?? $itemTypeName, count($arr) > 1);
    }

    /**
     * @param array<string, mixed> $docComments
     * @param string $type
     * @return PhpClassPropertyType
     */
    private function getDocCommentByType(array $docComments, string $type): PhpClassPropertyType
    {
        return isset($docComments[$type])
            ? $this->mapPropertyType($docComments[$type])
            : new PhpClassPropertyType();
    }

    /**
     * @param array<string, mixed> $docComments
     * @return null|PhpClassPropertyType
     */
    private function getTypeFromDocComment(array $docComments): ?PhpClassPropertyType
    {
        if (!isset($docComments['avro-type'])) return null;

        return $this->mapPropertyType($docComments['avro-type']);
    }

    /**
     * @param array<string, mixed> $docComments
     * @return string|int|float|null
     */
    private function getDefaultFromDocComment(array $docComments)
    {
        if (false === isset($docComments['avro-default'])) {
            return PhpClassPropertyInterface::NO_DEFAULT;
        }

        if (PhpClassPropertyInterface::EMPTY_STRING_DEFAULT === $docComments['avro-default']) {
            return '';
        }

        if (true === is_string($docComments['avro-default']) && true === is_numeric($docComments['avro-default'])) {
            $docComments['avro-default'] = $this->convertStringToNumber($docComments['avro-default']);
        }

        if ('null' === $docComments['avro-default']) {
            return null;
        }

        return $docComments['avro-default'];
    }

    /**
     * @param string $number
     * @return float|int
     */
    private function convertStringToNumber(string $number)
    {
        $int = (int) $number;

        if (strval($int) == $number) {
            return $int;
        }

        return (float) $number;
    }

    /**
     * @param array<string, mixed> $docComments
     * @return string|null
     */
    private function getLogicalTypeFromDocComment(array $docComments): ?string
    {
        return $docComments['avro-logical-type'] ?? null;
    }

    /**
     * @param array<string, mixed> $docComments
     * @return string|null
     */
    private function getDocFromDocComment(array $docComments): ?string
    {
        return $docComments['avro-doc'] ?? $docComments[DocCommentParserInterface::DOC_DESCRIPTION] ?? null;
    }

    /**
     * @param Property $property
     * @return array<string, mixed>
     */
    private function getAllPropertyDocComments(Property $property): array
    {
        $docComments = [];

        foreach ($property->getAttributes() as $attributeName => $attributeValue) {
            if ('comments' === $attributeName) {
                /** @var Doc $comment */
                foreach ($attributeValue as $comment) {
                    $docComments = array_merge($docComments, $this->docParser->parseDoc($comment->getText()));
                }
            }
        }

        return $docComments;
    }

    /**
     * @return array<string,null|string>
     */
    private function getEmptyAttributesArray(): array
    {
        return [
            'name' => null,
            'types' => null,
            'default' => PhpClassPropertyInterface::NO_DEFAULT,
            'logicalType' => null,
            'doc' => null
        ];
    }
}
