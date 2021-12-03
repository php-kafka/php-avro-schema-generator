<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Optimizer;

interface OptimizerInterface
{
    public function optimize(string $definition): string;
}
