<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Registry;

use PhpKafka\PhpAvroSchemaGenerator\Exception\ClassRegistryException;
use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClassInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
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
        $registry = new ClassRegistry();
        $result = $registry->addClassDirectory('/tmp');

        self::assertInstanceOf(ClassRegistryInterface::class, $result);
        self::assertEquals(['/tmp' => 1], $result->getClassDirectories());
    }

    public function testLoad()
    {
        $classDir = __DIR__ . '/../../../example/classes';

        $registry = (new ClassRegistry())->addClassDirectory($classDir)->load();

        self::assertInstanceOf(ClassRegistryInterface::class, $registry);

        $classes = $registry->getClasses();

        self::assertCount(4, $classes);

        foreach ($classes as $class) {
            self::assertInstanceOf(PhpClassInterface::class, $class);
        }
    }

    public function testRegisterSchemaFileThatDoesntExist()
    {
        $fileInfo = new SplFileInfo('somenonexistingfile');
        $registry = new ClassRegistry();

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

        $registry = new ClassRegistry();

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
