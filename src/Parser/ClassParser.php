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
     * @return class-string|null
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
                            return $this->buildClassName($nsStatement->extends->getParts());
                        }
                    }
                }
            } else {
                if ($statement instanceof Class_) {
                    if (null !== $statement->extends) {
                        return $this->buildClassName($statement->extends->getParts());
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return class-string[]
     */
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
                            $className = $use->name->getParts()[array_key_last($use->name->getParts())];
                            $usedClasses[$className] = $this->buildClassName($use->name->getParts());
                        }
                    }
                }
            }
        }

        return $usedClasses;
    }

    /**
     * @param string[] $parts
     * @return class-string
     */
    public function buildClassName(array $parts): string
    {
        /** @var class-string $classname */
        $classname = implode('\\', $parts);

        return $classname;
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
        $usedClasses = $this->getUsedClasses();
        $parentClass = $this->getParentClassName();

        if (null === $parentClass) {
            return [];
        }

        if (array_key_exists($parentClass, $usedClasses) && null !== $usedClasses[$parentClass]) {
            $parentClass = $usedClasses[$parentClass];
        }

        $rc = new ReflectionClass($parentClass);
        $filename = $rc->getFileName();

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
