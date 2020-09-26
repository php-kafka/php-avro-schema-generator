<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\PhpClass;

use PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClass;
use PHPUnit\Framework\TestCase;

/**
 * @covers PhpKafka\PhpAvroSchemaGenerator\PhpClass\PhpClass
 */
class PhpClassTest extends TestCase
{
    public function testGetters()
    {
        $phpClass = new PhpClass('TestClass', 'Test\\Space', 'some php code', []);

        self::assertEquals('TestClass', $phpClass->getClassName());
        self::assertEquals('Test\\Space', $phpClass->getClassNamespace());
        self::assertEquals('some php code', $phpClass->getClassBody());
        self::assertEquals([], $phpClass->getClassProperties());
    }
}
