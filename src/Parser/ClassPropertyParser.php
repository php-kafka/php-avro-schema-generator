<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\Avro\Avro;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use RuntimeException;

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
     * @param Property|mixed $property
     * @return PhpClassPropertyInterface
     */
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

    /**
     * @param Property $property
     * @return array<string, mixed>
     */
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

    private function getPropertyName(Property $property): string
    {
        return $property->props[0]->name->name;
    }

    /**
     * @param Property $property
     * @param array<string,mixed> $docComments
     * @return string
     */
    private function getPropertyType(Property $property, array $docComments): string
    {
        if ($property->type instanceof NullableType) {
            if ($property->type->type instanceof Identifier) {
                $type = Avro::MAPPED_TYPES[$property->type->type->name] ?? $property->type->type->name;
                return 'null|' . $type;
            }
        } elseif ($property->type instanceof Identifier) {
            return Avro::MAPPED_TYPES[$property->type->name] ?? $property->type->name;
        } elseif ($property->type instanceof UnionType) {
            $types = '';
            $separator = '';
            /** @var Identifier $type */
            foreach ($property->type->types as $type) {
                $type = Avro::MAPPED_TYPES[$type->name] ?? $type->name;
                $types .= $separator . $type;
                $separator = '|';
            }

            return $types;
        }

        return $this->getDocCommentByType($docComments, 'var') ?? 'string';
    }

    /**
     * @param array<string, mixed> $docComments
     * @return mixed
     */
    private function getDocCommentByType(array $docComments, string $type)
    {
        return $docComments[$type] ?? null;
    }

    /**
     * @param array<string, mixed> $docComments
     * @return string|null
     */
    private function getTypeFromDocComment(array $docComments): ?string
    {
        return $docComments['avro-type'] ?? null;
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
