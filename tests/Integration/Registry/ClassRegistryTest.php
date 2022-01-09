<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Registry;

use PhpKafka\PhpAvroSchemaGenerator\Converter\PhpClassConverter;
use PhpKafka\PhpAvroSchemaGenerator\Exception\ClassRegistryException;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\ClassPropertyParser;
use PhpKafka\PhpAvroSchemaGenerator\Parser\DocCommentParser;
use PhpKafka\PhpAvroSchemaGenerator\Avro\AvroRecordInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry
 */
class ClassRegistryTest extends TestCase
{
    public function testClassDirectory()
    {
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = new ClassRegistry($converter);
        $result = $registry->addClassDirectory('/tmp');

        self::assertInstanceOf(ClassRegistryInterface::class, $result);
        self::assertEquals(['/tmp' => 1], $result->getClassDirectories());
    }

    public function testLoad()
    {
        $classDir = __DIR__ . '/../../../example/classes';

        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = (new ClassRegistry($converter))->addClassDirectory($classDir)->load();

        self::assertInstanceOf(ClassRegistryInterface::class, $registry);

        $classes = $registry->getClasses();

        self::assertCount(4, $classes);

        foreach ($classes as $class) {
            self::assertInstanceOf(AvroRecordInterface::class, $class);
        }
    }

    public function testRegisterSchemaFileThatDoesntExist()
    {
        $fileInfo = new SplFileInfo('somenonexistingfile');
        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
        $converter = new PhpClassConverter($parser);
        $registry = new ClassRegistry($converter);

        self::expectException(ClassRegistryException::class);
        self::expectExceptionMessage(ClassRegistryException::FILE_PATH_EXCEPTION_MESSAGE);

        $reflection = new ReflectionClass(ClassRegistry::class);
        $method = $reflection->getMethod('registerClassFile');
        $method->setAccessible(true);
        $method->invokeArgs($registry, [$fileInfo]);
    }

    public function testRegisterSchemaFileThatIsNotReadable()
    {
        touch('testfile');
        chmod('testfile', 222);

        $fileInfo = new SplFileInfo('testfile');

        $propertyParser = new ClassPropertyParser(new DocCommentParser());
        $parser = new ClassParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7), $propertyParser);
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
            unlink('testfile');
        }
    }
}
