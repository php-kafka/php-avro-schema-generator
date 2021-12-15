<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;

class ClassPropertyParser implements ClassPropertyParserInterface
{

    public function parseProperty($property): PhpClassPropertyInterface
    {
        $propertyAttributes = $this->getPropertyAttributes($pStatement);

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
        $attributes = $this->getAvroAttributesFromCode($property);
        $attributes['name'] = $property->props[0]->name->name;

        return $attributes;
    }

    private function getAvroAttributesFromCode(Property $property): array
    {
        $attributes = $this->getEmptyAttributesArray();
        $attributes['types'] = $this->getPropertyType($property);

        return $attributes;
    }

    private function getPropertyDocComments(Property $property): array
    {
        $docComments = [];

        foreach ($property->getAttributes() as $attributeName => $attributeValue) {
            if ('comments' === $attributeName) {
                /** @var Doc $comment */
                foreach ($attributeValue as $comment) {
                    $docComments[] = $comment->getText();
                }
            }

        }

        return $docComments;
    }

    private function getPropertyType(Property $property): string
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

        return 'string';
    }

    private function getTypeFromDocComment(array $docComments): string
    {
        foreach ($docComments as $doc) {
            if (false !== $varPos = strpos($doc, '@var')) {
                if (false !== $eolPos = strpos($doc, PHP_EOL, $varPos)) {
                    $varDoc = substr($doc, $varPos, ($eolPos - $varPos));
                } else {
                    $varDoc = substr($doc, $varPos);
                }
                $rawTypes = trim(str_replace(['[]', '*/', '@var'], '', $varDoc));

                $types = explode('|', $rawTypes);

                foreach ($types as $type) {
                    if ('array' === $type) {
                        continue;
                    }

                    return $this->mappedTypes[$type] ?? $type;
                }
            }
        }

        return 'string';
    }

    private function getDefaultFromDocComment(): ?string
    {

    }

    private function getLogicalTypeFromDocComment(): ?string
    {

    }

    private function getDocFromDocComment(): ?string
    {

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