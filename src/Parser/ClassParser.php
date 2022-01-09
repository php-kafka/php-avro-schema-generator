<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassPropertyInterface;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use ReflectionClass;
use ReflectionException;

final class ClassParser implements ClassParserInterface
{
    private ClassPropertyParserInterface $propertyParser;
    private Parser $parser;

    /** @var Stmt[]|null  */
    private ?array $statements;

    public function __construct(Parser $parser, ClassPropertyParserInterface $propertyParser)
    {
        $this->parser = $parser;
        $this->propertyParser = $propertyParser;
    }

    public function setCode(string $code): void
    {
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
                        if ($nsStatement->name instanceof Identifier) {
                            return $nsStatement->name->name;
                        }
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
            } else {
                if ($statement instanceof Class_) {
                    if (null !== $statement->extends) {
                        return implode('\\', $statement->extends->parts);
                    }
                }
            }
        }

        return null;
    }

    public function getUsedClasses(): array
    {
        $usedClasses = [];

        if (null === $this->statements) {
            return $usedClasses;
        }

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
                if ($statement->name instanceof Name) {
                    return implode('\\', $statement->name->parts);
                }
            }
        }

        return null;
    }

    /**
     * @return PhpClassPropertyInterface[]
     */
    public function getProperties(): array
    {
        $properties = $this->getClassProperties($this->statements ?? []);

        $parentStatements = $this->getParentClassStatements();

        if (true === is_array($parentStatements)) {
            $properties = array_merge($properties, $this->getClassProperties($parentStatements));
        }

        return $properties;
    }

    /**
     * @param Stmt[] $statements
     * @return PhpClassPropertyInterface[]
     */
    private function getClassProperties(array $statements): array
    {
        $properties = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                foreach ($statement->stmts as $nsStatement) {
                    if ($nsStatement instanceof Class_) {
                        $properties = $this->getAllClassProperties($nsStatement, $properties);
                    }
                }
            } elseif ($statement instanceof Class_) {
                $properties = $this->getAllClassProperties($statement, $properties);
            }
        }

        return $properties;
    }

    /**
     * @param Class_ $class
     * @param PhpClassPropertyInterface[] $properties
     * @return PhpClassPropertyInterface[]
     */
    private function getAllClassProperties(Class_ $class, array $properties): array
    {
        foreach ($class->stmts as $pStatement) {
            if ($pStatement instanceof Property) {
                $properties[] = $this->propertyParser->parseProperty($pStatement);
            }
        }

        return $properties;
    }

    /**
     * @return Stmt[]|null
     * @throws ReflectionException
     */
    private function getParentClassStatements(): ?array
    {
        /** @var class-string[] $usedClasses */
        $usedClasses = $this->getUsedClasses();

        try {
            $pc = (new ReflectionClass($this->getNamespace() . '\\' . $this->getClassName()))->getParentClass();
        }
        catch (\ReflectionException $e) {
            throw new ReflectionException("Can't get parent class for [{$this->getNamespace()}\\{$this->getClassName()}]!", $e->getCode(), $e);
        }
        if (false === $pc) {
            return [];
        }

        $filename = $pc->getFileName();

        if (false === $filename) {
            return [];
        }

        $parentClass = file_get_contents($filename);

        if (false === $parentClass) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        return $this->parser->parse($parentClass);
    }
}
