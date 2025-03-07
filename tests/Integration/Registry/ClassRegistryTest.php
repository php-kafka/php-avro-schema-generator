<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Registry;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverter;
use PhpKafka\PhpAvroSchemaGenerator\Exception\ClassRegistryException;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry
 */
class ClassRegistryTest extends TestCase
{
    public function testClassDirectory(): void
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->createForVersion(PhpVersion::fromComponents(8,2)), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = new ClassRegistry($converter);
        $result = $registry->addClassDirectory('/tmp');

        self::assertInstanceOf(ClassRegistryInterface::class, $result);
        self::assertEquals(['/tmp' => 1], $result->getClassDirectories());
    }

    public function testLoad(): void
    {
        $classDir = __DIR__ . '/../../../example/classes';

        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->createForVersion(PhpVersion::fromComponents(8,2)), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = (new ClassRegistry($converter))->addClassDirectory($classDir)->load();

        self::assertInstanceOf(ClassRegistryInterface::class, $registry);

        $classes = $registry->getClasses();

        self::assertCount(4, $classes);

        foreach ($classes as $class) {
            self::assertInstanceOf(PhpClassInterface::class, $class);
        }
    }

    public function testRegisterSchemaFileThatDoesntExist(): void
    {
        $fileInfo = new SplFileInfo('somenonexistingfile');
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->createForVersion(PhpVersion::fromComponents(8,2)), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = new ClassRegistry($converter);

        self::expectException(ClassRegistryException::class);
        self::expectExceptionMessage(ClassRegistryException::FILE_PATH_EXCEPTION_MESSAGE);

        $reflection = new ReflectionClass(ClassRegistry::class);
        $method = $reflection->getMethod('registerClassFile');
        $method->setAccessible(true);
        $method->invokeArgs($registry, [$fileInfo]);
    }

    public function testRegisterSchemaFileThatIsNotReadable(): void
    {
        $filePath = '/tmp/test/testfile';
        touch($filePath);
        chmod($filePath, 222);

        $fileInfo = new SplFileInfo($filePath);

        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->createForVersion(PhpVersion::fromComponents(8,2)), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = new ClassRegistry($converter);

        self::expectException(ClassRegistryException::class);
        self::expectExceptionMessage(
            sprintf(ClassRegistryException::FILE_NOT_READABLE_EXCEPTION_MESSAGE, $fileInfo->getRealPath())
        );

        $reflection = new ReflectionClass(ClassRegistry::class);
        $method = $reflection->getMethod('registerClassFile');
        $method->setAccessible(true);
        try {
            $method->invokeArgs($registry, [$fileInfo]);
        } finally {
            @unlink($filePath);
        }
    }
}
