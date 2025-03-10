<?php

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Integration\Registry;

use PhpKafka\PhpAvroSchemaGenerator\Exception\SchemaRegistryException;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistryInterface;
use PhpKafka\PhpAvroSchemaGenerator\Schema\SchemaTemplateInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;

/**
 * @covers \PhpKafka\PhpAvroSchemaGenerator\Registry\SchemaRegistry
 */
class SchemaRegistryTest extends TestCase
{
    public function testSchemaDirectories(): void
    {
        $registry = new SchemaRegistry();
        $result = $registry->addSchemaTemplateDirectory('/tmp');

        self::assertInstanceOf(SchemaRegistryInterface::class, $result);
        self::assertEquals(['/tmp' => 1], $result->getSchemaDirectories());
    }

    public function testLoad(): void
    {
        $schemaIds = [
            'Book',
            'com.example.CD',
            'com.example.Collection',
            'com.example.Page',
            'com.example.Library',
            'primitive'
        ];

        $schemaDir = __DIR__ . '/../../../example/schemaTemplates';
        $registry = (new SchemaRegistry())->addSchemaTemplateDirectory($schemaDir)->load();
        $schemas = $registry->getSchemas();

        self::assertCount(6, $schemas);

        /** @var SchemaTemplateInterface $schema */
        foreach ($schemas as $schema) {
            self::assertInstanceOf(SchemaTemplateInterface::class, $schema);
            self::assertContains($schema->getSchemaId(), $schemaIds);
        }

        $expectedNames = ['CD', 'Collection', 'Page', 'Library'];

        $actualNamespaces = $registry->getSchemaNamesPerNamespace('com.example');

        self::assertSame(sort($expectedNames), sort($actualNamespaces));
    }

    public function testGetRootSchemas(): void
    {
        $schemaDir = __DIR__ . '/../../../example/schemaTemplates';
        $registry = (new SchemaRegistry())->addSchemaTemplateDirectory($schemaDir)->load();

        $rootSchemas = $registry->getRootSchemas();

        self::assertCount(3, $rootSchemas);

        foreach ($rootSchemas as $rootSchema) {
            self::assertInstanceOf(SchemaTemplateInterface::class, $rootSchema);
        }
    }

    public function testGetSchemaByIdNotExisting(): void
    {
        $registry = new SchemaRegistry();

        self::assertNull($registry->getSchemaById('test'));
    }

    public function testGetSchemaById(): void
    {
        $template = $this->getMockForAbstractClass(SchemaTemplateInterface::class);

        $registry = new SchemaRegistry();

        $reflection = new ReflectionClass($registry);
        $property = $reflection->getProperty('schemas');
        $property->setAccessible(true);
        $property->setValue($registry, ['test' => $template]);

        self::assertEquals($template, $registry->getSchemaById('test'));
    }

    public function testRegisterSchemaFileThatDoesntExist(): void
    {
        $fileInfo = new SplFileInfo('somenonexistingfile');
        $registry = new SchemaRegistry();

        self::expectException(SchemaRegistryException::class);
        self::expectExceptionMessage(SchemaRegistryException::FILE_PATH_EXCEPTION_MESSAGE);

        $reflection = new ReflectionClass(SchemaRegistry::class);
        $method = $reflection->getMethod('registerSchemaFile');
        $method->setAccessible(true);
        $method->invokeArgs($registry, [$fileInfo]);
    }

    public function testRegisterSchemaFileThatIsNotReadable(): void
    {
        $filePath = '/tmp/test/testfile';
        touch($filePath);
        chmod($filePath, 222);

        $fileInfo = new SplFileInfo($filePath);
        $registry = new SchemaRegistry();

        self::expectException(SchemaRegistryException::class);
        self::expectExceptionMessage(
            sprintf(SchemaRegistryException::FILE_NOT_READABLE_EXCEPTION_MESSAGE, $fileInfo->getRealPath())
        );

        $reflection = new ReflectionClass(SchemaRegistry::class);
        $method = $reflection->getMethod('registerSchemaFile');
        $method->setAccessible(true);
        try {
            $method->invokeArgs($registry, [$fileInfo]);
        } finally {
            @unlink($filePath);
        }
    }

    public function testRegisterSchemaWithInvalidContent(): void
    {
        $filePath = '/tmp/test/testfile';
        touch($filePath);

        $fileInfo = new SplFileInfo($filePath);

        $registry = new SchemaRegistry();

        self::expectException(SchemaRegistryException::class);
        self::expectExceptionMessage(
            sprintf(SchemaRegistryException::FILE_INVALID, $fileInfo->getRealPath())
        );

        $reflection = new ReflectionClass(SchemaRegistry::class);
        $method = $reflection->getMethod('registerSchemaFile');
        $method->setAccessible(true);
        try {
            $method->invokeArgs($registry, [$fileInfo]);
        } finally {
            @unlink($filePath);
        }
    }
}
