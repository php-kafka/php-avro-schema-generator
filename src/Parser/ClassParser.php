<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\ParserFactory;
use PhpParser\Parser;
use ReflectionClass;
use ReflectionException;

class ClassParser implements ClassParserInterface
{
    private ClassPropertyParserInterface $propertyParser;
    private Parser $parser;
    private string $code;
    private array $statements;

    public function __construct(Parser  $parser, ClassPropertyParserInterface $propertyParser)
    {
        $this->parser = $parser;
        $this->propertyParser = $propertyParser;
    }

    public function setCode(string $code): void
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
     * @return string|null
     */
    public function getParentClassName(): ?string
    {
        if (null === $this->statements) {
            return null;
        }

        foreach ($this->statements as $statement) {
            if ($statement instanceof Namespace_) {
                foreach ($statement->stmts as $nsStatement) {
                    if ($nsStatement instanceof Class_) {
                        if (null !== $nsStatement->extends) {
                            return implode('\\', $nsStatement->extends->parts);
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getUsedClasses(): array
    {
        $usedClasses = [];

        foreach ($this->statements as $statement) {
            if ($statement instanceof Namespace_) {
                foreach ($statement->stmts as $nStatement) {
                    if ($nStatement instanceof Use_) {
                        /** @var UseUse $use */
                        foreach ($nStatement->uses as $use) {
                            $className = $use->name->parts[array_key_last($use->name->parts)];
                            $usedClasses[$className] = implode('\\', $use->name->parts);
                        }
                    }
                }
            }
        }

        return $usedClasses;
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
        $properties = $this->getClassProperties($this->statements);

        $parentStatements = $this->getParentClassStatements();

        if (true === is_array($parentStatements)) {
            $properties = array_merge($properties, $this->getClassProperties($parentStatements));
        }

        return $properties;
    }

    private function getClassProperties(array $statements): array
    {
        $properties = [];

        foreach ($statements as $statement) {
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

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getParentClassStatements(): ?array
    {
        $usedClasses = $this->getUsedClasses();
        $rc = new ReflectionClass($usedClasses[$this->getParentClassName()]);
        $filename = $rc->getFileName();

        return $this->parser->parse(file_get_contents($filename));
    }
}