<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use RuntimeException;

class ClassPropertyParser implements ClassPropertyParserInterface
{
    private DocCommentParserInterface $docParser;

    /**
     * @var string[]
     */
    private $mappedTypes = array(
        'null' => 'null',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'string' => 'string',
        'int' => 'int',
        'integer' => 'int',
        'float' => 'float',
        'double' => 'double',
        'array' => 'array',
        'object' => 'object',
        'callable' => 'callable',
        'resource' => 'resource',
        'mixed' => 'mixed',
        'Collection' => 'array',
    );

    public function __construct(DocCommentParserInterface $docParser)
    {
        $this->docParser = $docParser;
    }

    public function parseProperty($property): PhpClassPropertyInterface
    {
        if (false === $property instanceof Property) {
            throw new RuntimeException(sprintf('Property must be of type: %s', Property::class));
        }

        $propertyAttributes = $this->getPropertyAttributes($property);

        return new PhpClassProperty(
            $propertyAttributes['name'],
            $propertyAttributes['types'],
            $propertyAttributes['default'],
            $propertyAttributes['doc'],
            $propertyAttributes['logicalType']
        );
    }


    private function getPropertyAttributes(Property $property): array
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

    private function getPropertyName(Property $property) {
        return $property->props[0]->name->name;
    }

    private function getPropertyType(Property $property, array $docComments): string
    {
        if ($property->type instanceof Identifier) {
            return $this->mappedTypes[$property->type->name] ?? $property->type->name;
        } elseif ($property->type instanceof UnionType) {
            $types = '';
            $separator = '';
            /** @var Identifier $type */
            foreach ($property->type->types as $type) {
                $types .= $separator . $this->mappedTypes[$type->name] ?? $type->name;
                $separator = ',';
            }

            return $types;
        }

        return $this->getDocCommentByType($docComments, 'var') ?? 'string';
    }

    private function getDocCommentByType(array $docComments, string $type)
    {
        return $docComments[$type] ?? null;
    }

    private function getTypeFromDocComment(array $docComments): ?string
    {
        return $docComments['avro-type'] ?? null;
    }

    private function getDefaultFromDocComment(array $docComments): string
    {
        return $docComments['avro-default'] ?? PhpClassPropertyInterface::NO_DEFAULT;
    }

    private function getLogicalTypeFromDocComment(array $docComments): ?string
    {
        return $docComments['avro-logical-type'] ?? null;
    }

    private function getDocFromDocComment(array $docComments): ?string
    {
        return $docComments['avro-doc'] ?? $docComments[DocCommentParserInterface::DOC_DESCRIPTION] ?? null;
    }

    private function getAllPropertyDocComments(Property $property): array
    {
        $docComments = [];

        foreach ($property->getAttributes() as $attributeName => $attributeValue) {
            if ('comments' === $attributeName) {
                /** @var Doc $comment */
                foreach ($attributeValue as $comment) {
                    $docComments += $this->docParser->parseDoc($comment->getText());
                }
            }

        }

        return $docComments;
    }

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