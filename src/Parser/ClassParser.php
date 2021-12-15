<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\ParserFactory;
use PhpParser\Parser;

class ClassParser implements ClassParserInterface
{
    private ParserFactory $parserFactory;
    private ClassPropertyParserInterface $propertyParser;
    private Parser $parser;
    private string $code;
    private array $statements;

    public function __construct(ParserFactory  $parserFactory, ClassPropertyParserInterface $propertyParser)
    {
        $this->parserFactory = $parserFactory;
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        $this->propertyParser = $propertyParser;
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
                                $properties[] = $this->propertyParser->parseProperty($pStatement);
                            }
                        }
                    }
                }
            }
        }

        return $properties;
    }
}