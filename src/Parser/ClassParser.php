<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PhpParser\ParserFactory;
use PhpParser\Parser;

class ClassParser
{
    private ParserFactory $parserFactory;
    private Parser $parser;
    private string $code;
    private array $statements;

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

    public function __construct(ParserFactory  $parserFactory)
    {
        $this->parserFactory = $parserFactory;
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);

    }

    public function setCode(string $code)
    {
        $this->code = $code;
        $this->statements = $this->parser->parse($code);
    }

    /**
     * @return string|null
     */
    public function getClassName(): ?string
    {
        if (null === $this->statements) {
            return null;
        }

        foreach ($this->statements as $statement) {
            if ($statement instanceof Namespace_) {
                foreach ($statement->stmts as $nsStatement) {
                    if ($nsStatement instanceof Class_) {
                        return $nsStatement->name->name;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getNamespace(): ?string
    {
        if (null === $this->statements) {
            return null;
        }

        foreach ($this->statements as $statement) {
            if ($statement instanceof Namespace_) {
                return implode('\\', $statement->name->parts);
            }
        }

        return null;
    }

    /**
     * @return PhpClassProperty[]
     */
    public function getProperties(): array
    {
        $properties = [];

        foreach ($this->statements as $statement) {
            if ($statement instanceof Namespace_) {
                foreach ($statement->stmts as $nsStatement) {
                    if ($nsStatement instanceof Class_) {
                        foreach ($nsStatement->stmts as $pStatement) {
                            if ($pStatement instanceof Property) {
                                $propertyAttributes = $this->getPropertyAttributes($pStatement);
                                $properties[] = new PhpClassProperty($propertyAttributes);
                            }
                        }
                    }
                }
            }
        }

        return $properties;
    }

    private function getPropertyAttributes(Property $property): PropertyAttributesInterface
    {
        $name = $property->props[0]->name->name;
        $attributes = $this->getAvroAttributesFromCode($property);
        var_dump($attributes);

        return new PropertyAttributes(
            $name,
            $attributes['types'],
            $attributes['default'],
            $attributes['logicalType'],
            $attributes['doc']
        );
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
            'default' => null,
            'doc' => null
        ];
    }
}