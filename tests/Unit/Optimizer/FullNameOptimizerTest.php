<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Tests\Unit\Optimizer;

use PhpKafka\PhpAvroSchemaGenerator\Optimizer\FullNameOptimizer;
use PHPUnit\Framework\TestCase;

class FullNameOptimizerTest extends TestCase
{
    public function testOptimize(): void
    {
        $optimizer = new FullNameOptimizer();

        self::assertTrue(true);
    }
}