<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Parser;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassProperty;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * Class taken and adapted from doctrine/annotations and PHP-DI/PhpDocReader
 * to avoid pulling the whole package.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christian Kaps <christian.kaps@mohiva.com>
 */
class TokenParser
{
    /**
     * The token list.
     *
     * @var array<int,mixed>
     */
    private $tokens;

    /**
     * The number of tokens.
     *
     * @var int
     */
    private $numTokens;

    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * The current array pointer.
     *
     * @var int
     */
    private $pointer = 0;

    /**
     * @var string[]
     */
    private $ignoredTypes = array(
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

    /**
     * @param string $contents
     */
    public function __construct($contents)
    {
        $this->tokens = token_get_all($contents);

        // The PHP parser sets internal compiler globals for certain things. Annoyingly, the last docblock comment it
        // saw gets stored in doc_comment. When it comes to compile the next thing to be include()d this stored
        // doc_comment becomes owned by the first thing the compiler sees in the file that it considers might have a
        // docblock. If the first thing in the file is a class without a doc block this would cause calls to
        // getDocBlock() on said class to return our long lost doc_comment. Argh.
        // To workaround, cause the parser to parse an empty docblock. Sure getDocBlock() will return this, but at least
        // it's harmless to us.
        token_get_all("<?php\n/**\n *\n */");

        $this->numTokens = count($this->tokens);
    }

    /**
     * @return string
     */
    public function getClassName(): ?string
    {
        if (true === isset($this->className)) {
            return $this->className;
        }

        for ($i = 0; $i < count($this->tokens); $i++) {
            if ($this->tokens[$i][0] === T_CLASS) {
                $this->className = $this->tokens[$i + 2][1];
                return $this->className;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        if ('' !== $this->namespace) {
            return $this->namespace;
        }

        for ($i = 0; $i < count($this->tokens); $i++) {
            if ($this->tokens[$i][0] === T_NAMESPACE) {
                $index = 2;
                while (true) {
                    if (true === is_string($this->tokens[$i + $index])) {
                        break 2;
                    }
                    $this->namespace .= $this->tokens[$i + $index][1];
                    ++$index;
                }
            }
        }

        return $this->namespace;
    }

    /**
     * Gets all use statements.
     *
     * @param string $namespaceName The namespace name of the reflected class.
     *
     * @return array<string|int,mixed> A list with all found use statements.
     * @codeCoverageIgnore
     */
    public function parseUseStatements($namespaceName)
    {
        $statements = array();
        while (($token = $this->next())) {
            if ($token[0] === T_USE) {
                $statements = array_merge($statements, $this->parseUseStatement());
                continue;
            }
            if ($token[0] !== T_NAMESPACE || $this->parseNamespace() != $namespaceName) {
                continue;
            }

            // Get fresh array for new namespace. This is to prevent the parser to collect the use statements
            // for a previous namespace with the same name. This is the case if a namespace is defined twice
            // or if a namespace with the same name is commented out.
            $statements = array();
        }

        $this->pointer = 0;

        return $statements;
    }

    /**
     * @param class-string $classPath
     * @return array<int, PhpClassProperty>
     * @throws \ReflectionException
     */
    public function getProperties(string $classPath): array
    {
        $properties = [];

        $reflectionClass = new ReflectionClass($classPath);

        foreach ($reflectionClass->getProperties() as $property) {
            $simpleType = (string) $this->getPropertyClass($property, false);
            $nestedType = (string) $this->getPropertyClass($property, true);
            $properties[] = new PhpClassProperty($property->getName(), $simpleType, $nestedType);
        }

        return $properties;
    }

    /**
     * Parse the docblock of the property to get the class of the var annotation.
     *
     * @param ReflectionProperty $property
     * @param boolean            $ignorePrimitive
     *
     * @throws \RuntimeException
     * @return string|null Type of the property (content of var annotation)
     * @codeCoverageIgnore
     */
    public function getPropertyClass(ReflectionProperty $property, bool $ignorePrimitive = true)
    {
        $type = null;
        /** @var false|string $phpVersionResult */
        $phpVersionResult = phpversion();
        $phpVersion = false === $phpVersionResult ? '7.0.0' : $phpVersionResult;
        // Get is explicit type declaration if possible
        if (version_compare($phpVersion, '7.4.0', '>=') && null !== $property->getType()) {
            $reflectionType = $property->getType();

            if ($reflectionType instanceof \ReflectionNamedType) {
                $type = $reflectionType->getName();
            }
        }

        if (is_null($type)) { // Try get the content of the @var annotation
            if (preg_match('/@var\s+([^\s]+)/', (string) $property->getDocComment(), $matches)) {
                list(, $type) = $matches;
            } else {
                return null;
            }
        }

        $types = explode('|', $this->replaceTypeStrings($type));

        foreach ($types as $type) {
            // Ignore primitive types
            if (true === isset($this->ignoredTypes[$type])) {
                if (false === $ignorePrimitive) {
                    return $this->ignoredTypes[$type];
                }

                if (true === $ignorePrimitive && 1 < count($types)) {
                    continue;
                }

                return null;
            }
            // Ignore types containing special characters ([], <> ...)
            if (!preg_match('/^[a-zA-Z0-9\\\\_]+$/', $type)) {
                return null;
            }
            $class = $property->getDeclaringClass();
            // If the class name is not fully qualified (i.e. doesn't start with a \)
            if ($type[0] !== '\\') {
                // Try to resolve the FQN using the class context
                $resolvedType = $this->tryResolveFqn($type, $class, $property);

                if (!$resolvedType) {
                    throw new \RuntimeException(sprintf(
                        'The @var annotation on %s::%s contains a non existent class "%s". '
                        . 'Did you maybe forget to add a "use" statement for this annotation?',
                        $class->name,
                        $property->getName(),
                        $type
                    ));
                }

                $type = $resolvedType;
            }

            if (!$this->classExists($type)) {
                throw new \RuntimeException(sprintf(
                    'The @var annotation on %s::%s contains a non existent class "%s"',
                    $class->name,
                    $property->getName(),
                    $type
                ));
            }

            // Remove the leading \ (FQN shouldn't contain it)
            $type = ltrim($type, '\\');
        }

        return $type;
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context.
     *
     * @param string $type
     * @param ReflectionClass $class
     * @param Reflector $member
     *
     * @return string|null Fully qualified name of the type, or null if it could not be resolved
     * @codeCoverageIgnore
     */
    private function tryResolveFqn($type, ReflectionClass $class, Reflector $member)
    {
        $alias = ($pos = strpos($type, '\\')) === false ? $type : substr($type, 0, $pos);
        $loweredAlias = strtolower($alias);
        // Retrieve "use" statements
        $parser = new TokenParser((string) file_get_contents((string) $class->getFileName()));
        $uses = $parser->parseUseStatements($class->getNamespaceName());

        if (isset($uses[$loweredAlias])) {
            // Imported classes
            if ($pos !== false) {
                return $uses[$loweredAlias] . substr($type, $pos);
            } else {
                return $uses[$loweredAlias];
            }
        } elseif ($this->classExists($class->getNamespaceName() . '\\' . $type)) {
            return $class->getNamespaceName() . '\\' . $type;
        } elseif (isset($uses['__NAMESPACE__']) && $this->classExists($uses['__NAMESPACE__'] . '\\' . $type)) {
            // Class namespace
            return $uses['__NAMESPACE__'] . '\\' . $type;
        } elseif ($this->classExists($type)) {
            // No namespace
            return $type;
        }
        if (version_compare((string) phpversion(), '5.4.0', '<')) {
            return null;
        } else {
            // If all fail, try resolving through related traits
            return $this->tryResolveFqnInTraits($type, $class, $member);
        }
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context, specifically searching
     * through the traits that are used by the provided $class.
     *
     * @param string $type
     * @param ReflectionClass $class
     * @param Reflector $member
     *
     * @return string|null Fully qualified name of the type, or null if it could not be resolved
     * @codeCoverageIgnore
     */
    private function tryResolveFqnInTraits($type, ReflectionClass $class, Reflector $member)
    {
        /** @var ReflectionClass[] $traits */
        $traits = array();
        // Get traits for the class and its parents
        while ($class) {
            $traits = array_merge($traits, $class->getTraits());
            $class = $class->getParentClass();
        }

        foreach ($traits as $trait) {
            // Eliminate traits that don't have the property/method/parameter
            if ($member instanceof ReflectionProperty && !$trait->hasProperty($member->name)) {
                continue;
            } elseif ($member instanceof ReflectionMethod && !$trait->hasMethod($member->name)) {
                continue;
            } elseif (
                $member instanceof ReflectionParameter
                && !$trait->hasMethod($member->getDeclaringFunction()->name)
            ) {
                continue;
            }
            // Run the resolver again with the ReflectionClass instance for the trait
            $resolvedType = $this->tryResolveFqn($type, $trait, $member);

            if ($resolvedType) {
                return $resolvedType;
            }
        }
        return null;
    }

    /**
     * Gets the next non whitespace and non comment token.
     *
     * @param boolean $docCommentIsComment If TRUE then a doc comment is considered a comment and skipped.
     *                                     If FALSE then only whitespace and normal comments are skipped.
     *
     * @return array<int,mixed>|null The token if exists, null otherwise.
     */
    private function next($docCommentIsComment = true)
    {
        for ($i = $this->pointer; $i < $this->numTokens; $i++) {
            $this->pointer++;
            if (
                $this->tokens[$i][0] === T_WHITESPACE
                || $this->tokens[$i][0] === T_COMMENT
                || ($docCommentIsComment
                    && $this->tokens[$i][0] === T_DOC_COMMENT)
            ) {
                continue;
            }

            return $this->tokens[$i];
        }

        return null;
    }

    /**
     * Parses a single use statement.
     *
     * @return array<string,class-string> A list with all found class names for a use statement.
     * @codeCoverageIgnore
     */
    private function parseUseStatement()
    {
        $class = '';
        $alias = '';
        $statements = array();
        $explicitAlias = false;
        while (($token = $this->next())) {
            $isNameToken = $token[0] === T_STRING || $token[0] === T_NS_SEPARATOR;
            if (!$explicitAlias && $isNameToken) {
                $class .= $token[1];
                $alias = $token[1];
            } elseif ($explicitAlias && $isNameToken) {
                $alias .= $token[1];
            } elseif ($token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } elseif ($token === ',') {
                $statements[strtolower($alias)] = $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } elseif ($token === ';') {
                $statements[strtolower($alias)] = $class;
                break;
            } else {
                break;
            }
        }

        return $statements;
    }

    /**
     * Gets the namespace.
     *
     * @return string The found namespace.
     */
    private function parseNamespace()
    {
        $name = '';
        while (($token = $this->next()) && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
            $name .= $token[1];
        }

        return $name;
    }

    /**
     * @param string $class
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }

    /**
     * @param string $type
     * @return string
     */
    private function replaceTypeStrings(string $type): string
    {
        return str_replace('[]', '', $type);
    }
}
